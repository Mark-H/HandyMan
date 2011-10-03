<?php
/**
 * Base abstract class for HandyMan action pages
 */
abstract class hmController {
    const VIEW_DIALOG = 'dialog';
    const VIEW_PAGE = 'page';
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
     * @return boolean
     */
    abstract public function setup();
    /**
     * Put the logic of your page here.
     * @abstract
     * @return void
     */
    abstract public function process();

    public final function initialize() {
        return $this->setup();
    }

    /**
     * Set the page title of your page by overriding this method
     * @return string
     */
    public function getPageTitle() {
        return 'HandyMan';
    }
    
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

    public function setPlaceholder($k,$v) {
        $this->placeholders[$k] = $v;
    }
    public function setPlaceholders(array $array = array()) {
        foreach ($array as $k => $v) {
            $this->setPlaceholder($k,$v);
        }
    }
    public function getPlaceholder($k,$default = null) {
        return isset($this->placeholders[$k]) ? $this->placeholders[$k] : $default;
    }
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

    protected function getHeader() {
        return $this->hm->getTpl('header',array_merge(
            array(
                'title' => $this->getPageTitle(),
                'config'
            ),
            $this->config)
        );
    }

    protected function getFooter() {
        return $this->hm->getTpl('footer');
    }

    protected function getLicense() {
        return $this->hm->getTpl('license',array(
            'license' => $this->hm->getLicenseName(),
        ));
    }

    protected function renderPageType($body = '') {
        $id = $this->getPageId();
        $id = !empty($id) ? $id : $this->config['hma'];
        $cache = $this->cache === false ? '' : ' data-dom-cache="true"';

        $output = '';
        $placeholders = array(
            'id' => $id,
            'cache' => $cache,
            'title' => $this->getPageTitle(),
            'content' => $body,
            'license' => $this->getLicense(),
            'baseUrl' => $this->hm->webroot,
            'userid' => $this->modx->user->get('id')
        );
        // Depending on the type of page (determined by the $this->viewType constant) we'll output something here.
        switch ($this->viewType) {
            // First "view" is a dialog window, which doesn't need as many buttons and stuff. We do add a "Close window" button here.
            case 'dialog':
                $output .= $this->hm->getTpl('views/dialog',$placeholders);
                break;

            // The default view is the "page" one, which has a back & home button and just the main content after that.
            case 'page':
            default:
                $output .= $this->hm->getTpl('views/page',$placeholders);
            break;
        }
        return $output;
    }

    public function processActions(array $actions = array()) {
        $output = array();
        foreach ($actions as $action) {
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
            $action['link'] = $this->hm->webroot.'index.php?hma='.$action['action'].$lps;
            
            $output[] = $this->hm->getTpl('widgets/rowAction',$action);
        }
        return implode("\n",$output);
    }

    public function redirect($action,array $params = array()) {
        $params['action'] = $action;
        $url = $this->hm->webroot.'index.php?'.http_build_query($params);
        $this->modx->sendRedirect($url);
    }
}