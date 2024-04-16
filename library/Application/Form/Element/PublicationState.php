<?PHP

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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\PublicationState;

class Application_Form_Element_PublicationState extends Application_Form_Element_SelectWithNull
{
    public function init()
    {
        parent::init();

        $this->setLabel($this->getView()->translate($this->getName()));

        $this->addMultiOption('Null', '-');

        $options = $this->getAllowedValues();

        foreach ($options as $state) {
            $this->addMultiOption($state, $this->translateValue($state));
        }
    }

    /**
     * @param string|null $value
     */
    public function setValue($value)
    {
        $options = $this->getMultiOptions();

        $publicationState = new PublicationState();

        if (! array_key_exists($value, $options) && in_array($value, $publicationState->getAllValues())) {
            $additionalOption[$value] = $this->translateValue($value);

            $options = array_merge(
                array_slice($options, 0, 1),
                $additionalOption,
                array_slice($options, 1)
            );

            $this->setMultiOptions($options);
        }

        parent::setValue($value);
    }

    /**
     * @return string[]
     */
    public function getAllowedValues()
    {
        $publicationState = new PublicationState();

        return $publicationState->getValues();
    }

    /**
     * @param string $value
     * @return string
     */
    public function translateValue($value)
    {
        $translationKey = "Opus_Document_PublicationState_Value_{$value}";
        return $this->getTranslator()->translate($translationKey);
    }
}