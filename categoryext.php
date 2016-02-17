<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  11.1
 */

class JFormFieldCategoryext extends JFormFieldCategory
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Categoryext';

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		$context_or_contenttype = $this->element['context_or_contenttype'] ? (string) $this->element['context_or_contenttype'] : (string) $this->element['scope'];
//~ dump ($context_or_contenttype,'$context_or_contenttype');

		$extension = $this->element[$context_or_contenttype] ? (string) $this->element[$context_or_contenttype] : (string) $this->element['scope'];
//~ dump ($extension,'$extension');
		switch ($context_or_contenttype) {
			case 'context':
				break;
			case 'content_type':
			default :
				$category = JTable::getInstance( 'contenttype' );
				$category->load( $extension );
				$extension = $category->type_alias;
				break;
		}
		$extension = explode ('.',$extension);
		$extension = $extension[0];
//~ dump ($extension,'$extension 2');


		$published = (string) $this->element['published'];

		// Load the category options for a given extension.
		if (!empty($extension))
		{
			switch ($extension) {
				case 'com_k2':
						// Get the database object.
						$db = JFactory::getDbo();

						$query = $db->getQuery(true);
						$query->select('id');
						$query->select('name');
						$query->from('#__k2_categories');
						if ($published) {
							$query->where($db->quoteName('published') .' = '. $db->Quote('1'));
						}
						// Set the query and get the result list.
						$db->setQuery((string)$query);
						$items = $db->loadObjectlist();
						// Build the field options.
						if (!empty($items))	{
							foreach ($items as $item)	{
									$options[] = JHtml::_('select.option', $item->id, $item->name);
							}
						}
					break;
				default :

					// Filter over published state or not depending upon if it is present.
					if ($published)
					{
						$options = JHtml::_('category.options', $extension, array('filter.published' => explode(',', $published)));
					}
					else
					{
						$options = JHtml::_('category.options', $extension);
					}

					// Verify permissions.  If the action attribute is set, then we scan the options.
					if ((string) $this->element['action'])
					{
						// Get the current user object.
						$user = JFactory::getUser();

						foreach ($options as $i => $option)
						{
							/*
							 * To take save or create in a category you need to have create rights for that category
							 * unless the item is already in that category.
							 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
							 */
							if ($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
							{
								unset($options[$i]);
							}
						}
					}
					break;
			}


			if (isset($this->element['show_root']))
			{
				array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
			}
		}
		else
		{
			JLog::add(JText::_('JLIB_FORM_ERROR_FIELDS_CATEGORY_ERROR_EXTENSION_EMPTY'), JLog::WARNING, 'jerror');
		}

		// Merge any additional options in the XML definition.
		/*##mygruz20160213194844 {
		$options = array_merge(parent::getOptions(), $options);
		It was:
		It became:*/
		/*##mygruz20160213194844 } */

		return $options;
	}
	protected function getInput() {
		$options = (array) $this->getOptions();

		if (empty($options)) {
			$formfield = JFormHelper::loadFieldType('text');
			$formfield->setup($this->element,'');
			if (is_array($this->value)) {
				$formfield->value = implode(',',$this->value);
			}
			else {
				$formfield->value = $this->value;
			}
			$formfield->hint = JText::_((string)$this->hint);
			return $formfield->getInput().PHP_EOL;
		}
		return parent::getInput();
	}
}
