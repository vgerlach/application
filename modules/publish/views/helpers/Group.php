<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Description of RenderForm
 *
 * @author Susanne Gottwald
 */
class View_Helper_Group extends Zend_View_Helper_Abstract {

    public $view;
    public $session;

    /**
     * method to render specific elements of an form
     * @param <type> $type element type that has to rendered
     * @param <type> $value value of element or Zend_Form_Element
     * @param <type> $name name of possible hidden element
     * @return element to render in view
     */
    public function group($value, $options = null, $name = null) {
        $this->session = new Zend_Session_Namespace('Publish');
        $log = Zend_Registry::get('Zend_Log');
        $this->session->elementCount = $this->session->elementCount + 1;
        $log->debug("elementCount = " . $this->session->elementCount . " for group" );

        if ($name == null && $value == null) {
            $error_message = $this->view->translate('template_error_unknown_field');
            return "<br/><div style='width: 400px; color:red;'>" . $error_message . "</div><br/><br/>";
        }
        else {            
            $result = $this->_renderGroup($value, $options, $name);
            return $result;
        }
    }

    /**
     * Method to render a group of elements (group fields, buttons, hidden fields)
     * @param <Array> $group
     */
    protected function _renderGroup($group, $options= null, $name = null) {
        $fieldset = "";
        $disable = false;
        if (isset($group)) {
            if ($this->session->currentAnchor === $group['Name'])
                    $fieldset .= "<a name='current'";
            $fieldset .= "<fieldset class='left-labels' id='". $group['Name'] ."' />\n";
            $fieldset .= "<legend>" . $this->view->translate($group['Name']) . "</legend>\n\t";            
            //$fieldset .= "<div class='form-multiple odd>'";

            //show fields
            foreach ($group["Fields"] AS $field) {
                $fieldset .= "\n<div class='form-item'>\n";
                $fieldset .= "<label for='" . $field["id"] . "'>" . $field["label"];
                if ($field["req"] === 'required')
                    $fieldset .= $this->_getRequiredSign();
                $fieldset .= "</label>";
                
                switch ($field['type']) {
                    case "Zend_Form_Element_Text":
                        $fieldset .= "\n\t\t\t\t<input type='text' class='form-textfield' name='" . $field["id"] . "' id='" . $field["id"] . "' ";
                        if ($options !== null)
                            $fieldset .= $options . " ";
                        else
                            $fieldset .= "size='30' ";

                        if ($field["disabled"] === true) {
                            $fieldset .= " disabled='1' ";
                            $disable = true;
                        }

                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";
                        $fieldset .= " value='" . $field["value"] . "' /></div>";

                        if (isset($field["desc"]))
                            $fieldset .= "<p class='description'>" . $this->view->translate($field["desc"]) . "</p>";
                        
                        break;

                    case "Zend_Form_Element_Textarea":
                        $fieldset .= "\n\t\t\t\t<textarea name='" . $field["id"] . "' class='form-textarea' ";
                        if ($options !== null)
                            $fieldset .= $options . " ";
                        else
                            $fieldset .= "cols='30' rows='10' ";

                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "' ";

                        $fieldset .= " id='" . $field["id"] . "'>" . $field["value"] . "</textarea></div>";
                        
                        break;

                    case "Zend_Form_Element_Select" :
                        $fieldset .= "\n\t\t\t\t<select name='" . $field["id"] . "' class='form-selectfield'  id='" . $field["id"] . "'";
                        if (strstr($field["id"], "1"))
                            $fieldset .= " title='" . $this->view->translate($field["hint"]) . "'";
                        $fieldset .= ">\n\t\t\t\t\t";

                        foreach ($field["options"] AS $key => $option) {
                            $fieldset .= "<option value='" . $key . "' label='" . $option . "'";

                            if ($option === $field["value"] || $key === $field["value"])
                                $fieldset .= " selected='selected'>";
                            else
                                $fieldset .= ">";
                            $fieldset .= $option . "</option>\n\t\t\t\t\t";
                        }
                        $fieldset .= "</select></div>";
                        break;

                    case 'Zend_Form_Element_Checkbox' :
                        $fieldset .= "<input type='hidden' name='" . $field['id'] . "' value='0' />";
                        $fieldset .= "\n\t\t\t\t<input type='checkbox' class='form-checkbox' name='" . $field['id'] . "' id='". $field['id'] ."' ";
                        $fieldset .= "title='". $this->view->translate($field['hint']) ."' value='". $field['value'] ."' ";
                        if ($field['value'] === '1')
                            $fieldset .= " checked='checked' />";
                        else
                            $fieldset .= " /></div>";
                        break;
                        
                    default:
                        throw new Application_Exception("Field Type {$field['type']} not found in View Helper.");
                }
                
                if ($field["error"] != null) {
                    $fieldset .= "<div class='form-errors'><ul>";
                    foreach ($field["error"] AS $err)
                        $fieldset .= "<li>" . $err . "</li>";
                    $fieldset .= "</ul></div>";
                }
                
            }
            //show buttons
            foreach ($group["Buttons"] AS $button) {
                if (!$disable) {
                    $fieldset .= "\n\t\t\t\t<div class='button-wrapper add-delete-wrapper'>";
                    $fieldset .= "<input type='submit' ";
                    if (strstr('add', $button['id']))
                            $fieldset .= "class='form-button add-button' ";
                    else {
                        if (strstr('delete', $button['id']))
                                $fieldset .= "class='form-button delete-button' ";
                    }

                    $fieldset .= "name='" . $button["id"] . "' id='" . $button["id"] . "' value='" . $button["label"] . "' />";
                }
            }
            //show hidden fields
            foreach ($group["Hiddens"] AS $hidden) {
                $fieldset .= "\n<input type='hidden' name='" . $hidden["id"] . "' id='" . $hidden["id"] . "' value='" . $hidden["value"] . "' />";
            }
            $fieldset .= "<div class='description hint'>" . $this->_getGroupHint($group['Name']) . "</div>";
            $fieldset .= "\n\n</fieldset>\n\n";
        }
        return $fieldset;
    }

    /**
     * returns the hint string for a required element
     * @return <type> 
     */
    protected function _getRequiredSign() {
        return "<span class='required' title='" . $this->view->translate('publish_controller_required_hint_sort') . "'>*</span>";
    }

    /**
     * returns a html String for displaying the group hint
     * @param <String> $groupName
     * @return <type>
     */
    protected function _getGroupHint($groupName) {
        return $this->view->translate('hint_' . $groupName);
    }

}

?>
