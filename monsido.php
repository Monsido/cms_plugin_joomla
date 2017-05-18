<?php defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * @package Monsido
 * @author Monsido
 * @copyright (C) 2017 - Monsido
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

class plgSystemMonsido extends JPlugin
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config	An optional associative array of configuration settings.
	 *
	 * @since   1.0
	 */
	public function __construct(&$subject, $config)
	{
		// Calling the parent Constructor
		parent::__construct($subject, $config);
	}

	/**
	 * Listener for the `onAfterRender` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$view   = $input->get('view');
		$layout = $input->get('layout');
		$id     = $input->get('id');
		$db     = JFactory::getDBO();

		if( $app->isSite() ) {
			if($view != 'article' && $layout != 'edit' ) return;
			
			$id = $input->get('a_id');
			
			$article = JControllerLegacy::getInstance('Content')
            ->getModel('Article')->getItem($id);
			
			$rawUri =  ContentHelperRoute::getArticleRoute($id, 
                      $article->catid, 
                      $article->language);
                      
			$uri = JRoute::_($rawUri, true, -1);
			$rawUri = JURI::base().$rawUri;
			
			$append = "<!-- Monsido: public_urls['{$uri}','{$rawUri}'] -->";

			//add url data before /body tag
			$body = JFactory::getApplication()->getBody();
			$body = str_replace('<head>', "<head>{$append}", $body);

			JFactory::getApplication()->setBody($body);
		}

		if( $app->isAdmin() ) {
			if($view != 'article' && $layout != 'edit' ) return;
			
			if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php');
			
			$id = $input->get('id');
			
			$article = JControllerLegacy::getInstance('Content')
            ->getModel('Article')->getItem($id);
			
			$rawUri =  ContentHelperRoute::getArticleRoute($id, 
                      $article->catid, 
                      $article->language);
            
            $app    = JApplication::getInstance('site');
			$router = &$app->getRouter();
			$uri = $router->build($rawUri);
			$parsed_url = str_replace('administrator/', '', $uri->toString());
			
			if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') || (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
			    $server_request_scheme = 'https';
			} else {
			    $server_request_scheme = 'http';
			}

			$uri = $server_request_scheme .'://'. $_SERVER['HTTP_HOST'].JRoute::_($parsed_url, true, -1);
			
			$rawUri = JURI::base().$rawUri;
			
			$append = "<!-- Monsido: public_urls['{$uri}','{$rawUri}'] -->";

			//add url data before /body tag
			$body = JFactory::getApplication()->getBody();
			$body = str_replace('<head>', "<head>{$append}", $body);

			JFactory::getApplication()->setBody($body);
		}
	}
}