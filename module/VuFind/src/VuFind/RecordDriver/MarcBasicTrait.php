<?php
/**
 * Functions to add basic MARC-driven functionality to a record driver not already
 * powered by the standard index spec. Depends upon MarcReaderTrait.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Ere Maijala <ere.maijala@helsinki.fi>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
namespace VuFind\RecordDriver;

/**
 * Functions to add basic MARC-driven functionality to a record driver not already
 * powered by the standard index spec. Depends upon MarcReaderTrait.
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
trait MarcBasicTrait
{
    /**
     * Get an array of all ISBNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISBNs()
    {
        return $this->getFieldArray('020');
    }

    /**
     * Get an array of all ISSNs associated with the record (may be empty).
     *
     * @return array
     */
    public function getISSNs()
    {
        return $this->getFieldArray('022');
    }

    /**
     * Get an array of all the formats associated with the record.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->getFieldArray('245', ['h']);
    }

    /**
     * Return the unique identifier of this record within the Solr index;
     * useful for retrieving additional information (like tags and user
     * comments) from the external MySQL database.
     *
     * @return string Unique identifier.
     */
    public function getUniqueID()
    {
        return (string)$this->getMarcRecord()->getField('001')->getData();
    }

    /**
     * Get the call numbers associated with the record (empty string if none).
     *
     * @return array
     */
    public function getCallNumbers()
    {
        $retVal = [];
        foreach (['090', '050'] as $field) {
            $callNo = $this->getFirstFieldValue($field, ['a', 'b']);
            if (!empty($callNo)) {
                $retVal[] = $callNo;
            }
        }
        $dewey = $this->getDeweyCallNumber();
        if (!empty($dewey)) {
            $retVal[] = $dewey;
        }
        return $retVal;
    }

    /**
     * Get the Dewey call number associated with this record (empty string if none).
     *
     * @return string
     */
    public function getDeweyCallNumber()
    {
        return $this->getFirstFieldValue('082', ['a']);
    }

    /**
     * Get the main authors of the record.
     *
     * @return array
     */
    public function getPrimaryAuthors()
    {
        return [$this->getFirstFieldValue('100', ['a'])];
    }

    /**
     * Get an array of all the languages associated with the record.
     *
     * @return array
     */
    public function getLanguages()
    {
        $retVal = [];
        $field = $this->getMarcRecord()->getField('008');
        if ($field) {
            $content = $field->getData();
            if (strlen($content) >= 38) {
                $retVal[] = substr($content, 35, 3);
            }
        }
        return $retVal;
    }

    /**
     * Get the full title of the record.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getFirstFieldValue('245', ['a', 'b']);
    }

    /**
     * Get a sortable title for the record (i.e. no leading articles).
     *
     * @return string
     */
    public function getSortTitle()
    {
        $field = $this->getMarcRecord()->getField('245');
        if ($field) {
            $title = $field->getSubfield('a');
            if ($title) {
                $skip = $field->getIndicator(2);
                return substr($title->getData(), $skip);
            }
        }
        return parent::getSortTitle();
    }

    /**
     * Get the short (pre-subtitle) title of the record.
     *
     * @return string
     */
    public function getShortTitle()
    {
        return $this->getFirstFieldValue('245', ['a']);
    }

    /**
     * Get the subtitle of the record.
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->getFirstFieldValue('245', ['b']);
    }

    /**
     * Get the publishers of the record.
     *
     * @return array
     */
    public function getPublishers()
    {
        return $this->getPublicationInfo('b');
    }

    /**
     * Get the publication dates of the record.  See also getDateSpan().
     *
     * @return array
     */
    public function getPublicationDates()
    {
        return $this->getPublicationInfo('c');
    }

    /**
     * Get an array of all secondary authors (complementing getPrimaryAuthors()).
     *
     * @return array
     */
    public function getSecondaryAuthors()
    {
        return $this->getFieldArray('700', ['a', 'b', 'c', 'd']);
    }

    /**
     * Get an array of newer titles for the record.
     *
     * @return array
     */
    public function getNewerTitles()
    {
        return $this->getFieldArray('785', ['a', 's', 't']);
    }

    /**
     * Get an array of previous titles for the record.
     *
     * @return array
     */
    public function getPreviousTitles()
    {
        return $this->getFieldArray('780', ['a', 's', 't']);
    }
}
