<?php

namespace LemoGrid\Adapter;

use IntlDateFormatter;
use LemoGrid\GridInterface;
use LemoGrid\ResultSet\JqGridResultSet;
use LemoGrid\ResultSet\ResultSetInterface;
use Locale;

abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Number of filtered items
     *
     * @var int
     */
    protected $countItems = 0;

    /**
     * Number of items
     *
     * @var int
     */
    protected $countItemsTotal = 0;

    /**
     * Is the grid prepared?
     *
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * @var ResultSetInterface
     */
    protected $resultSet;

    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Konvertuje zadane casti datumu na DB date format pro vyhledavani pomoci LIKE
     *
     * @param string $value
     * @return string
     */
    protected function convertLocaleDateToDbDate($value)
    {
        // Zjistime aktualni strukturu podle Locale
        $formatter = new IntlDateFormatter(Locale::getDefault(), IntlDateFormatter::SHORT, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN);
        $pattern = $formatter->getPattern();

        // Zjistime zvoleny separator a poradi dne a mesice
        $patternSeparators = ['.', '/', '-', ' '];
        $separator = null;
        foreach ($patternSeparators as $patternSeparator) {
            if(strpos($pattern, $patternSeparator)){
                $splitPattern = str_split($pattern);
                $first = 'month';
                $second = 'day';
                $firstPatternChar = strtolower($splitPattern[0]);
                if ('d' == $firstPatternChar || 'j' == $firstPatternChar ) {
                    $first = 'day';
                    $second = 'month';
                }
                $separator = $patternSeparator;
                break;
            }
        }

        if($separator){
            $dateDb = [];

            // Pokud je datumem
            // https://bugs.php.net/bug.php?id=68528
            // IntlDateFormatter::parse() throws warnings on not parsable date on windows only
            if (false !== @$formatter->parse($value)) {
                $timestamp = $formatter->parse($value);

                $dateDb['day'] = date('d', $timestamp);
                $dateDb['month'] = date('m', $timestamp);
                $dateDb['year'] = date('Y', $timestamp);

                // je ve formatu napr. ".12.2014" nebo "12.2014"
            } elseif(preg_match('/^\\' . $separator . '\d{1,2}\\' . $separator . '\d{4}$/',$value, $matches)
                || preg_match('/^\d{1,2}\\' . $separator . '\d{4}$/', $value, $matches)) {
                list($dateDb[$second], $dateDb['year']) = explode($separator, trim($matches[0], $separator));

                // je ve formatu napr. "24.12." nebo "24.12"
            } elseif(preg_match('/^\d{1,2}\\' . $separator . '\d{1,2}\\' . $separator . '$/',$value, $matches)
                || preg_match('/^\d{1,2}\\' . $separator . '\d{1,2}$/', $value, $matches)) {
                list($dateDb[$first], $dateDb[$second]) = explode($separator, trim($matches[0], $separator));

                // je ve formatu napr. "2014"
            } elseif(preg_match('/^\d{4}$/',$value,$matches)) {
                $dateDb['year'] = $matches[0];

                // je ve formatu napr. ".12." nebo ".12"
            } elseif (preg_match('/^\\' . $separator . '\d{1,2}\\' . $separator . '$/', $value, $matches)
                || preg_match('/^\\' . $separator . '\d{1,2}$/', $value, $matches)) {
                if ('y' == $firstPatternChar) {
                    $dateDb[$first] = trim($matches[0], $separator);
                } else {
                    $dateDb[$second] = trim($matches[0], $separator);
                }

                // je ve formatu napr. "24."
            } elseif (preg_match('/^\d{1,2}\\' . $separator . '$/', $value,$matches)) {
                if ('y' == $firstPatternChar) {
                    $dateDb[$second] = trim($matches[0], $separator);
                } else {
                    $dateDb[$first] = trim($matches[0], $separator);
                }
            } else {
                $dateDb[$second] = trim($value);
            }

            // Pripravime date DB fragmenty z casti resultSet
            $string = '';
            if (isset($dateDb['year'])) {
                $string .= $dateDb['year'] . '-';
            }
            if (isset($dateDb['month'])) {
                if (!isset($dateDb['year'])) {
                    $string .= '-';
                }
                $string .= str_pad($dateDb['month'], 2, '0', STR_PAD_LEFT ) . '-';
            }
            if (isset($dateDb['day'])) {
                if (!isset($dateDb['year']) && !isset($dateDb['month'])) {
                    $string .= '-';
                }
                $string .= str_pad($dateDb['day'], 2, '0', STR_PAD_LEFT );
            }

            return $string;
        }
    }

    /**
     * Get number of current page
     *
     * @return int
     */
    public function getNumberOfPages()
    {
        $numberOfPages = ceil($this->getCountOfItemsTotal() / $this->getGrid()->getPlatform()->getNumberOfVisibleRows());

        if ($numberOfPages < 1) {
            $numberOfPages = 1;
        }

        return $numberOfPages;
    }

    /**
     * Return count of items
     *
     * @return int
     */
    public function getCountOfItems()
    {
        return $this->countItems;
    }

    /**
     * Return count of items total
     *
     * @return int
     */
    public function getCountOfItemsTotal()
    {
        return $this->countItemsTotal;
    }

    /**
     * Set grid instance
     *
     * @param  GridInterface $grid
     * @return AbstractAdapter
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Get grid instance
     *
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared()
    {
        return $this->isPrepared;
    }
}
