<?php
/*
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
 * @package     Controller
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller helper for providing workflow support.
 */
class Controller_Helper_Workflow extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Basic workflow configuration.
     * @var Zend_Config_Ini
     */
    private static $__workflowConfig;

    /**
     * Gets called when helper is used like method of the broker.
     * @param Opus_Document $document
     * @return array of strings - Allowed target states for document
     */
    public function direct($document) {
        return $this->getAllowedTargetStatesForDocument($document);
    }

    /**
     * Returns true if a requested state is valid.
     * @param string $state
     * @return boolean TRUE - only if the state string exists
     */
    public function isValidState($state) {
        $states = $this->getAllStates();

        return in_array($state, $states);
    }

    /**
     * Returns true if a transition is allowed for a document.
     * @param Opus_Document $document
     * @param string $targetState
     * @return boolean - True only if transition is allowed
     */
    public function isTransitionAllowed($document, $targetState) {
        $allowedStates = $this->getAllowedTargetStatesForDocument($document);

        return in_array($targetState, $allowedStates);
    }

    /**
     * Returns all allowed target states for a document.
     * @param Opus_Document $document
     * @return array of strings - Possible target states for document
     */
    public function getAllowedTargetStatesForDocument($document) {
        $currentState = $document->getServerState();

        return $this->getTargetStates($currentState);
    }

    /**
     * Returns all allowed target states for a current state.
     * @param string $currentState All lowercase name of current state
     * @return array of strings - Possible target states for document
     */
    public function getTargetStates($currentState) {
        // special code to handle 'removed' state
        if ($currentState === 'removed') {
            return array();
        }

        $workflow = self::getWorkflowConfig();

        $targetStates = $workflow->get($currentState);

        if (!empty($targetStates)) {
            return $targetStates->toArray();
        }
        else {
            return array();
        }
    }

    /**
     * Performs state change on document.
     * @param Opus_Document $document
     * @param string $targetState
     *
     * TODO enforcing permissions and throwing exceptions (OPUSVIER-1959)
     */
    public function changeState($document, $targetState) {
        switch ($targetState) {
            case 'published':
                $document->setServerState($targetState);
                $date = new Zend_Date();
                $document->setServerDatePublished(
                        $date->get('yyyy-MM-ddThh:mm:ss') . 'Z');
                $document->store();
                break;
            case 'deleted':
                $document->delete();
                break;
            case 'removed':
                $document->deletePermanent();
                break;
            default:
                $document->setServerState($targetState);
                $document->store();
                break;
        }
    }

    /**
     * Returns all defined states of workflow model.
     * @return array of string Names of defined states
     */
    public function getAllStates() {
        $workflow = self::getWorkflowConfig();

        return array_keys($workflow->toArray());
    }

    /**
     * Returns configuration for basic workflow model.
     * @return Zend_Config_Ini
     */
    public static function getWorkflowConfig() {
        if (empty(Controller_Helper_Workflow::$__workflowConfig)) {
            Controller_Helper_Workflow::$__workflowConfig = new Zend_Config_Ini(
                    APPLICATION_PATH . '/modules/admin/models/workflow.ini');
        }

        return Controller_Helper_Workflow::$__workflowConfig;
    }

}