<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * AltRotate Content Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Content.AltRotate
 * @since		1.5
 */
class plgContentAltRotate extends JPlugin
{
	
	protected static $modules = array();
 
	protected static $mods = array();
	/**
	 * AltRotate after delete method.
	 *
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that was deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onContentAfterDelete($context, $data)
	{
		return true;
	}

	/**
	 * AltRotate after display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string  The context for the content passed to the plugin.
	 * @param   object		The content object.  Note $article->text is also available
	 * @param   object		The content params
	 * @param   integer  	The 'page' number
	 * @return  string
	 * @since   1.6
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * AltRotate after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param	string		The context of the content passed to the plugin (added in 1.6)
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @since	1.6
	 */
	public function onContentAfterSave($context, &$article, $isNew)
	{
		$app = JFactory::getApplication();

		return true;
	}

	/**
	 * AltRotate after display title method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  Note $article->text is also available
	 * @param	object		The content params
	 * @param	int			The 'page' number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentAfterTitle($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * AltRotate before delete method.
	 *
	 * @param	string	The context for the content passed to the plugin.
	 * @param	object	The data relating to the content that is to be deleted.
	 * @return	boolean
	 * @since	1.6
	 */
	public function onContentBeforeDelete($context, $data)
	{
		return true;
	}

	/**
	 * AltRotate before display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param   string  The context for the content passed to the plugin.
	 * @param   object		The content object.  Note $article->text is also available
	 * @param   object		The content params
	 * @param   integer  	The 'page' number
	 * @return  string
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();

		return '';
	}

	/**
	 * AltRotate before save content method
	 *
	 * Method is called right before content is saved into the database.
	 * Article object is passed by reference, so any changes will be saved!
	 * NOTE:  Returning false will abort the save with an error.
	 *You can set the error by calling $article->setError($message)
	 *
	 * @param	string		The context of the content passed to the plugin.
	 * @param	object		A JTableContent object
	 * @param	bool		If the content is just about to be created
	 * @return	bool		If false, abort the save
	 * @since	1.6
	 */
	public function onContentBeforeSave($context, &$article, $isNew)
	{
		$app = JFactory::getApplication();

		return true;
	}

	/**
	 * AltRotate after delete method.
	 *
	 * @param   string	The context for the content passed to the plugin.
	 * @param   array	A list of primary key ids of the content that has changed state.
	 * @param   integer  The value of the state that the content has been changed to.
	 * @return  boolean
	 * @since   1.6
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		return true;
	}

	/**
	 * AltRotate prepare content method
	 *
	 * Method is called by the view
	 *
	 * @param   string	The context of the content being passed to the plugin.
	 * @param   object	The content object.  Note $article->text is also available
	 * @param   object	The content params
	 * @param   integer  The 'page' number
	 * @since   1.6
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
		$this->altRotatateContent($article, $params, $page = 0);
	}
	
	// The main function
	function altRotatateContent(&$row, &$params, $page = 0) {
		$member = $this->getGroupId("Members");
		$registered = $this->getGroupId("Registered");
		$guest = $this->getGroupId("Guest");
		
		$tagReplace = array(
			"displayreg" => "1",
			"displayguest" => "1",
			"displaymem" => "1",
		);
		
		// API
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$document  = JFactory::getDocument();

		// Assign paths
		$sitePath = JPATH_SITE;
		$siteUrl  = JURI::root(true);
		if (version_compare(JVERSION, '2.5.0', 'ge')) {
			$pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name.'/'.$this->plg_name;
		} else {
			$pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name;
		}

		// Check if plugin is enabled
		if (JPluginHelper::isEnabled('content',$this->plg_name)==false) return;

		// Load the plugin language file the proper way
		JPlugin::loadLanguage('plg_content_'.$this->plg_name, JPATH_ADMINISTRATOR);
			
		// Simple performance check to determine whether plugin should process further
		$grabTags = strtolower(implode(array_keys($tagReplace),"|"));
		//if (preg_match("#{(".$grabTags.")(.*?)}#is", $row->text)==false) return;
				
		// Get plugin info
		$plugin = JPluginHelper::getPlugin('content', $this->plg_name);

		// Control external parameters and set variable for controlling plugin layout within modules
		if (!$params) {
			$params = class_exists('JParameter') ? new JParameter(null) : new JRegistry(null);
		}
		$parsedInModule = $params->get('parsedInModule');

		$pluginParams = class_exists('JParameter') ? new JParameter($plugin->params) : new JRegistry($plugin->params);
		
		//-- get user id --//
		$user = JFactory::getUser();        // Get the user object
		//$app  = JFactory::getApplication(); // Get the application
		$groups = $user->get('groups');
		$uGrp = array();
		foreach($groups as $group) {
			$uGrp[$group]=1;
		}
							
		$regexmod = "#{(\w+)\s(.*?)}#is";
		//$regexmod = "#{displayguest\s(.*?)}#is";
		
		preg_match_all($regexmod, $row->text, $matchesmod, PREG_SET_ORDER);
	
		//$this->log($uGrp);
		
		// If no matches, skip this
		if ($matchesmod)
		{
			foreach ($matchesmod as $matchmod)
			{
				if($matchmod[1] == "displayguest")
				{
					$module = "custom";
					$name = trim($matchmod[2]);
					$style = null;
					
					if($uGrp[$guest] == 1)
					{
						$output = $this->_loadmod($module, $name, $stylemod);
					}else{
						$output = "";
					}
					// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
						$row->text = preg_replace("|$matchmod[0]|", addcslashes($output, '\\$'), $row->text, 1);
						$stylemod = $this->params->def('style', 'none');
				}
				
				if($matchmod[1] == "displayreg")
				{
					$module = "custom";
					$name = trim($matchmod[2]);
					$style = null;
					
					if($uGrp[$registered] == 1)
					{
						$output = $this->_loadmod($module, $name, $stylemod);
					}else{
						$output = "";
					}
					// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
						$row->text = preg_replace("|$matchmod[0]|", addcslashes($output, '\\$'), $row->text, 1);
						$stylemod = $this->params->def('style', 'none');
				
				}
				
				if($matchmod[1] == "displaymem")
				{
					$module = "custom";
					$name = trim($matchmod[2]);
					$style = null;
					
					if($uGrp[$member] == 1)
					{
						$output = $this->_loadmod($module, $name, $stylemod);
					}else{
						$output = "";
					}
					// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
						$row->text = preg_replace("|$matchmod[0]|", addcslashes($output, '\\$'), $row->text, 1);
						$stylemod = $this->params->def('style', 'none');
				
				}
			}
		}
		
		//-- match inline instead of module --//
		$tagsReplace = array(
			"registered" => "0",
			"guest" => "0",
			"member" => "0",
		);
		
		$grabTags = strtolower(implode(array_keys($tagsReplace),"|"));
		if (preg_match("#{(".$grabTags.")}#is", $row->text)==false) return;
		
		$tagsReplace = array_change_key_case($tagsReplace, CASE_LOWER);
		foreach ($tagsReplace as $plg_tag => $value) {
			// expression to search for
			$regex = "#{".$plg_tag."}.*?{/".$plg_tag."}#is";
			
			// process tags
			if (preg_match_all($regex, $row->text, $matches, PREG_PATTERN_ORDER)) {
				
				// start the replace loop
				foreach ($matches[0] as $key => $match) {
					$tagcontent 	= preg_replace("/{.+?}/", "", $match);
					$tagcontent		= str_replace(array('"','\'','`'), array('&quot;','&apos;','&#x60;'), $tagcontent); // Address potential XSS attacks
					$tagparams 		= explode('|',$tagcontent);
					$tagsource 		= trim(strip_tags($tagparams[0]));
					
					// Prepare the HTML
					$output = new JObject;
					
					if($plg_tag == "guest"){
						if($uGrp[$guest] == 1)
						{
							$output = $tagcontent;
						}else{
							$output = "";
						}
							// Output
							//$row->text = preg_replace("#{guest}".preg_quote($tagcontent)."{/guest}#is", $output , $row->text);
							$text_before = substr($row->text, 0, strpos($row->text, "{".$plg_tag."}"));
							$text_after = substr($row->text, (strpos($row->text, "{/".$plg_tag."}")+strlen("{/".$plg_tag."}")));
							$row->text = $text_before.$output.$text_after;
					}
					
					if($plg_tag == "member"){
						if($uGrp[$member] == 1)
						{
							$output = $tagcontent;
						}else{
							$output = "";
						}
							// Output
							//$row->text = preg_replace("#{member}".preg_quote($tagcontent)."{/member}#is", $output , $row->text);
							$text_before = substr($row->text, 0, strpos($row->text, "{".$plg_tag."}"));
							$text_after = substr($row->text, (strpos($row->text, "{/".$plg_tag."}")+strlen("{/".$plg_tag."}")));
							$row->text = $text_before.$output.$text_after;
					}
					
					if($plg_tag == "registered"){
						if($uGrp[$registered] == 1)
						{
							$output = $tagcontent;
						}else{
							$output = "";
						}
							// Output
							//$row->text = preg_replace("#{registered}".preg_quote($tagcontent)."{/registered}#is", $output , $row->text);
							
							$text_before = substr($row->text, 0, strpos($row->text, "{".$plg_tag."}"));
							$text_after = substr($row->text, (strpos($row->text, "{/".$plg_tag."}")+strlen("{/".$plg_tag."}")));
							$row->text = $text_before.$output.$text_after;
							
					}
					
				}
			}
		}
	}
	
	/**
	 * Loads and renders the module
	 *
	 * @param   string  $position  The position assigned to the module
	 * @param   string  $style     The style assigned to the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _load($position, $style = 'none')
	{
		self::$modules[$position] = '';
		$document	= JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$modules	= JModuleHelper::getModules($position);
		$params		= array('style' => $style);
		ob_start();
 
		foreach ($modules as $module)
		{
			echo $renderer->render($module, $params);
		}
 
		self::$modules[$position] = ob_get_clean();
 
		return self::$modules[$position];
	}
 
	/**
	 * This is always going to get the first instance of the module type unless
	 * there is a title.
	 *
	 * @param   string  $module  The module title
	 * @param   string  $title   The title of the module
	 * @param   string  $style   The style of the module
	 *
	 * @return  mixed
	 *
	 * @since   1.6
	 */
	protected function _loadmod($module, $title, $style = 'none')
	{
		self::$mods[$module] = '';
		$document	= JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$mod		= JModuleHelper::getModule($module, $title);
 
		// If the module without the mod_ isn't found, try it with mod_.
		// This allows people to enter it either way in the content
		if (!isset($mod))
		{
			$name = 'mod_'.$module;
			$mod  = JModuleHelper::getModule($name, $title);
		}
 
		$params = array('style' => $style);
		ob_start();
 
		echo $renderer->render($mod, $params);
 
		self::$mods[$module] = ob_get_clean();
 
		return self::$mods[$module];
	}
	
	/***
	*		Log some data for debugging
	*
	*
	******/
	function log($data)
	{
		// -- DEBUGGING --//
		
		$file = JPATH_BASE . DS . 'tmp/'.time();
		touch($file);
		global $current; 
		$current = file_get_contents($file);
		$current .= "---\n";
		$current .= print_r($data, true);

		file_put_contents($file, $current);
		//-- DEBUGGING --//
	}
	
	function getGroupId($groupName){
		$db = JFactory::getDBO();
		$db->setQuery($db->getQuery(true)
			->select('*')
			->from("#__usergroups")
		);
		$groups = $db->loadRowList();
		foreach ($groups as $group) {
			if ($group[4] == $groupName) // $group[4] holds the name of current group
				return $group[0];        // $group[0] holds group ID
		}
		return false; // return false if group name not found
	}
	

}
