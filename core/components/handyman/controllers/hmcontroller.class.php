<?php
/**
 * Base abstract class for HandyMan action pages
 */
abstract class hmController {
    const VIEW_DIALOG = 'dialog';
    const VIEW_PAGE = 'page';
    const VIEW_PAGE_LOGGEDOUT = 'page_loggedout';
    const LIST_DIVIDER = 'divider';
    protected $templateFile = 'empty';
    protected $viewType = self::VIEW_PAGE;
    protected $cache = true;

    /** @var HandyMan $hm */
    public $hm;
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = array();
    /** @var array $placeholders */
    protected $placeholders = array();

    /**
     * hmController constructor
     * @param \HandyMan $hm
     * @param array $config
     * @return \hmController
     */
    function __construct(HandyMan &$hm,array $config = array()) {
        $this->hm =& $hm;
        $this->modx =& $hm->modx;
        $this->config = array_merge($this->hm->config,$this->config,$config);
    }

    /**
     * Setup the page. Used for grabbing, say, the Resource being edited/viewed. Return true to proceed;
     * anything other than true will be interpreted as an error message (such as "Resource not found!" and will be
     * outputted.
     *
     * @abstract
     * @return boolean|string
     */
    abstract public function setup();
    /**
     * Put the logic of your page here.
     * @abstract
     * @return void
     */
    abstract public function process();

    /**
     * Initialize the controller. Runs the setup() method.
     * @return bool
     */
    public final function initialize() {
        return $this->setup();
    }

    /**
     * Set the page title of your page by overriding this method
     * @return string
     */
    public function getPageTitle() {
        return $this->modx->lexicon('handyman');
    }

    /**
     * You can override a Page ID here which is used in the outputted HTML.
     */
    public function getPageId() {}

    /**
     * Render the page.
     * 
     * @final
     * @param array $options An array of page-specific options, including details about the page
     * @return string
     */
    public final function render(array $options = array()) {
        $this->config = array_merge($this->config,$options);
        $this->process();
        $output = $this->hm->getTpl($this->templateFile,$this->placeholders);
        $output = $this->wrap($output);

        return $output;
    }

    /**
     * Set a placeholder.
     * @param $k
     * @param $v
     */
    public function setPlaceholder($k,$v) {
        $this->placeholders[$k] = $v;
    }

    /**
     * Set placeholders.
     * @param array $array
     */
    public function setPlaceholders(array $array = array()) {
        foreach ($array as $k => $v) {
            $this->setPlaceholder($k,$v);
        }
    }

    /**
     * @param $k Key of the placeholder
     * @param string $default Value of the placeholder
     * @return null|string
     */
    public function getPlaceholder($k,$default = null) {
        return isset($this->placeholders[$k]) ? $this->placeholders[$k] : $default;
    }

    /**
     * Returns all placeholders currently set.
     * @return array
     */
    public function getPlaceholders() {
        return $this->placeholders;
    }

    /**
     * Wrap the page in the header and footer.
     * 
     * @param string $body
     * @return string
     */
    public function wrap($body = '') {
        $output = $this->renderPageType($body);
        return $this->getHeader().$output.$this->getFooter();
    }

    /**
     * Get the header template, with title and other placeholders.
     * @return string
     */
    protected function getHeader() {
        return $this->hm->getTpl(
            'header',
            array_merge(
                array('title' => $this->getPageTitle()),
                $this->config,
                $this->placeholders
            )
        );
    }

    /**
     * Get the footer template, can use placeholders
     * @return string
     */
    protected function getFooter() {
        return $this->hm->getTpl(
            'footer',
            array_merge(
                array('title' => $this->getPageTitle()),
                $this->config,
                $this->placeholders
            ));
    }

    /**
     * Get the license template.
     * Due to refactoring in a future release.
     *
     * @return string
     */
    protected function getLicense() {
        return $this->hm->getTpl('license',array(
            'license' => $this->hm->getLicenseName(),
        ));
    }

    /**
     * Render the page depending on the hmController::$viewType var.
     * @param string $body
     * @return string
     */
    protected function renderPageType($body = '') {
        $id = $this->getPageId();
        $id = !empty($id) ? $id : $this->config['hma'];
        $cache = $this->cache === false ? '' : ' data-dom-cache="true"';

        $output = '';
        $placeholders = array_merge($this->config,array(
            'id' => $id,
            'cache' => $cache,
            'title' => $this->getPageTitle(),
            'content' => $body,
            'license' => $this->getLicense(),
            'userid' => $this->modx->user->get('id')
        ));

        // Depending on the type of page (determined by the $this->viewType constant) we'll output something here.
        switch ($this->viewType) {
            case hmController::VIEW_DIALOG:
                $output .= $this->hm->getTpl('views/dialog',$placeholders);
            break;

            case hmController::VIEW_PAGE_LOGGEDOUT:
                $output .= $this->hm->getTpl('views/page_loggedout',$placeholders);
            break;

            case hmController::VIEW_PAGE:
            default:
                $output .= $this->hm->getTpl('views/page',$placeholders);
            break;
        }
        return $output;
    }

    /**
     * Process a multi level array of actions into a JQM list.
     * @param array $actions
     * @return string
     */
    public function processActions(array $actions = array()) {
        $output = array();
        foreach ($actions as $action) {
            if (!empty($action[hmController::LIST_DIVIDER])) {
                $output[] = $this->hm->getTpl('widgets/dividerli',array('text' => $action[hmController::LIST_DIVIDER]));
            } else {
                if (isset($action['dialog'])) {
                    $action['dialog'] = ' data-rel="dialog"';
                    $action['transition'] = $action['transition'] ? $action['transition'] : 'pop';
                } else {
                    $action['transition'] = $action['transition'] ? $action['transition'] : 'slide';
                    $action['dialog'] = '';
                }
                $action['icon'] = $action['icon'] ? $action['icon'] : 'arrow-r';
                $lps = '';
                if (count($action['linkparams']) > 0) {
                    foreach ($action['linkparams'] as $lp => $lpv) {
                        $lps .= '&'.$lp.'='.$lpv;
                    }
                }
                $action['link'] = $this->hm->url.'?hma='.$action['action'].$lps;

                $output[] = $this->hm->getTpl('widgets/rowAction',$action);
            }
        }
        return implode("\n",$output);
    }

    /**
     * Send a redirect.
     *
     * @param $action
     * @param array $params
     */
    public function redirect($action,array $params = array()) {
        $params['action'] = $action;
        $url = $this->hm->url.'index.php?'.http_build_query($params);
        $this->modx->sendRedirect($url);
    }
}