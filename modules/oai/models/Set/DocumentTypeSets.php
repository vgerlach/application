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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentInterface;
use Opus\Common\Repository;

/**
 * Document type based OAI sets.
 *
 * Only document types actually used by documents are taken into account, for the document types the DC-Type is used.
 */
class Oai_Model_Set_DocumentTypeSets extends Application_Model_Abstract implements Oai_Model_Set_SetTypeInterface
{
    private const SET_NAME = 'doc-type';

    /**
     * Returns oai sets for document types.
     *
     * @param DocumentInterface|null $document
     * @return array
     */
    public function getSets($document = null)
    {
        $logger       = $this->getLogger();
        $dcTypeHelper = new Application_View_Helper_DcType();
        $sets         = [];

        if ($document) {
            $type           = $document->getType();
            $dcType         = $dcTypeHelper->dcType($type);
            $setSpec        = self::SET_NAME . ':' . $dcType;
            $sets[$setSpec] = ucfirst($dcType);
        } else {
            $finder = Repository::getInstance()->getDocumentFinder();
            $finder->setServerState('published');

            foreach ($finder->getDocumentTypes() as $doctype) {
                if (! Oai_Model_Set_SetName::isValidSubsetName($doctype)) {
                    $msg = "Invalid SetSpec (doctype='" . $doctype . "')."
                        . " Allowed characters are [" . Oai_Model_Set_SetName::SET_PART_PATTERN . "].";
                    $logger->err("OAI-PMH: $msg");
                    continue;
                }

                $dcType = $dcTypeHelper->dcType($doctype);

                $setSpec        = self::SET_NAME . ':' . $dcType;
                $sets[$setSpec] = ucfirst($dcType);
            }
        }

        return $sets;
    }

    /**
     * Configures the passed Finder according to the specified set.
     *
     * @param DocumentFinderInterface $finder
     * @param Oai_Model_Set_SetName   $setName
     * @throws Oai_Model_Exception
     */
    public function configureFinder($finder, $setName)
    {
        $subsetName = $setName->getSubsetName();

        if ($setName->getSetPartsCount() !== 2) {
            throw new Oai_Model_Exception(
                'The given set results in an empty list: ' . $setName->getFullSetName(),
                Oai_Model_Error::NORECORDSMATCH
            );
        }

        $dcTypeHelper  = new Application_View_Helper_DcType();
        $documentTypes = $dcTypeHelper->documentTypes($subsetName);

        $finder->setDocumentType($documentTypes);
    }

    /**
     * Returns if the set type class supports the handling of given set name.
     *
     * @param Oai_Model_Set_SetName $setName
     * @return bool
     */
    public function supports($setName)
    {
        return $setName->getSetName() === self::SET_NAME;
    }
}