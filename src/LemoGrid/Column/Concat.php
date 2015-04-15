<?php

namespace LemoGrid\Column;

use DateTime;
use LemoGrid\Adapter\AdapterInterface;
use LemoGrid\Exception;
use Traversable;

class Concat extends AbstractColumn
{
    /**
     * Column options
     *
     * @var ConcatOptions
     */
    protected $options;

    /**
     * Set column options
     *
     * @param  array|\Traversable|ConcatOptions $options
     * @throws Exception\InvalidArgumentException
     * @return Concat
     */
    public function setOptions($options)
    {
        if (!$options instanceof ConcatOptions) {
            if (is_object($options) && !$options instanceof Traversable) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Expected instance of LemoGrid\Column\ConcatOptions; '
                    . 'received "%s"', get_class($options))
                );
            }

            $options = new ConcatOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Get column options
     *
     * @return ConcatOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions(new ConcatOptions());
        }

        return $this->options;
    }

    /**
     * @param  AdapterInterface $adapter
     * @param  array            $item
     * @return string
     */
    public function renderValue(AdapterInterface $adapter, array $item)
    {
        $value = null;
        $patternValues = array();

        foreach ($this->getOptions()->getIdentifiers() as $index => $identifier) {
            $valuesIdentifier = $adapter->findValue($identifier, $item);

            if (!empty($valuesIdentifier)) {

                if (!is_array($valuesIdentifier)) {
                    $valuesIdentifier = array($valuesIdentifier);
                }

                foreach ($valuesIdentifier as $valueIndex => $valueIdentifier) {
                    if($valueIdentifier instanceof DateTime) {
                        $valueIdentifier = $value->format('Y-m-d H:i:s');
                    }

                    $patternValues[$valueIndex]['%s' . $index] = $valueIdentifier;
                }
            }
        }

        // Slozime jednotlive casti na radak
        foreach ($patternValues as $patternValue) {
            if (!empty($patternValue)) {
                $values[] = $this->patternEvaluate($this->getOptions()->getPattern(), $patternValue);
            } else {
                $values[] = null;
            }
        }

        if (empty($values)) {
            return null;
        }

        return implode($this->getOptions()->getSeparator(), $values);
    }

    /**
     * @param  string $pattern
     * @param  array  $patternValues
     * @return string
     */
    protected function patternEvaluate($pattern, $patternValues)
    {
        $value = '';

        if (!empty($patternValues)) {
            $patternValuesToReplace = array();

            // Odstranime hodnoty casti patternu, ktere nemaji prazdnou hodnotu
            foreach ($patternValues as $key => $patternValue) {
                if ('' == $patternValue) {
                    unset($patternValues[$key]);
                }
            }

            // Pridame indexy jednotlivym znakum pro nahrazeni
            $patternExploded = explode('%s', $pattern);
            $pattern = '';
            foreach ($patternExploded as $index => $pat) {
                $prefix = '';
                if ($index > 0) {
                    $prefix = '%s' . ($index - 1);

                    // Vytvorime hodnoty k nahrazeni, vcetne prazdneho stringu
                    $patternValuesToReplace[$prefix] = (isset($patternValues[$prefix])) ? $patternValues[$prefix] : ' ';
                }

                $pattern .= $prefix . $pat;
            }

            // Nacteme jednotlive casti patternu (dle zavorek)
            if (false === strpos($pattern, '(')) {
                $matches[] = $pattern;
            } else {
                preg_match_all('~(?= ( \( (?> [^()]++ | (?1) )* \) ) )~x', $pattern, $matches);
                $matches = $matches[1];
            }

            // Seradime klice obracene, aby se nahrazovaly odzadu (od nejvyssiho zanoreni)
            krsort($matches);

            // Najdeme si casti, ktere maji jen jednu cast k nahrazeni a nebyl nacten retezec
            $partsValues = array();
            foreach ($matches as $matchIndex => $match) {
                $matchOriginal = $match;

                // Nahradime v aktualni casti vyrazi, ktere jsou jiz vyhodnocene
                $match = str_replace(array_keys($partsValues), array_values($partsValues), $match);

                // Rozdelime si vyraz na dvojice se separatorem
                $parts = $this->patternEvaluateParse($match);

                // Projdeme dvojice a vyhodnotime, zda maji oba vyrazy
                foreach ($parts as $part) {

                    // Doplnime do patternu casti hodnoty
                    $partValue = str_replace(array_keys($patternValues), array_values($patternValues), $part);

                    // Zjistime si pocet nahrazenych znaku a nenahrazenych znaku
                    preg_match_all('/%s[0-9]{1}?/', $partValue, $partExpressionsNotReplaced);
                    preg_match_all('/%s[0-9]{1}?/', $part, $partExpressions);

                    // Odstranime separator mezi 2 castmi
                    if (count($partExpressionsNotReplaced[0]) > 0) {

                        // Muzeme odstranit separator?
                        if (preg_match('/{(.*)}?/', $part)) {

                            // Odstranime cast patternu, ktera predchazi {}
                            $separatorPattern = substr($pattern, strpos($pattern, $part));
                            $separatorPattern = substr($separatorPattern, strpos($separatorPattern, '}') + 1);

                            // Doplnime do paternu separatoru hodnoty
                            $separatorValue = str_replace(array_keys($patternValues), array_values($patternValues), $separatorPattern);

                            // Zjistime si pocet nahrazenych znaku a nenahrazenych znaku
                            preg_match_all('/%s[0-9]{1}?/', $separatorPattern, $separatorExpressions);
                            preg_match_all('/%s[0-9]{1}?/', $separatorValue, $separatorExpressionsNotReplaced);

                            // Pokud nebyla nahrazena zadna hodnota, odstranime separator
                            if (count($separatorExpressions[0]) == count($separatorExpressionsNotReplaced[0])) {
                                $match = str_replace($part, implode('', $partExpressions[0]), $match);
                            } else {
                                $match = str_replace($partExpressionsNotReplaced[0], '', $match);
                            }
                        } else {
                            $match = str_replace($part, implode('', $partExpressions[0]), $match);
                        }
                    }
                }

                // Odstranime z patternu znaky, ktere urciji neodlucitelny separator
                $match = str_replace(array('{', '}'), '', $match);

                // Zjistime, zda ma cast zavorky a pokud ano, tak je odstranime
                $partPattern = $match;
                $partHasBrackets = false;
                if (substr($partPattern, 0, 1) == '(' && substr($partPattern, -1, 1) == ')') {
                    if (substr($partPattern, 0, 1) == '(') {
                        $partPattern = substr($partPattern, 1);
                    }
                    if (substr($partPattern, -1, 1) == ')') {
                        $partPattern = substr($partPattern, 0, -1);
                    }

                    $partHasBrackets = true;
                }

                // Vytvorime hodnotu pro cast
                $valueWithRealData = str_replace(array_keys($patternValues), array_values($patternValues), $partPattern);
                $value = str_replace(array_keys($patternValuesToReplace), array_values($patternValuesToReplace), $partPattern);
                $value = trim($value);
                $value = preg_replace('#\s+#', ' ', $value);

                // Zjistime, zda byly nahrazene veskere hodnoty
                preg_match_all('/%s[0-9]{1}?/', $valueWithRealData, $valueNotReplacedExpressions);
                preg_match_all('/%s[0-9]{1}?/', $match, $valueExpressions);

                if (count($valueNotReplacedExpressions[0]) != count($valueExpressions[0])) {
                    if (true === $partHasBrackets && '' !== $value) {
                        $value = '(' . $value . ')';
                    }
                }

                $partsValues = array_merge(array($matchOriginal => $value), $partsValues);
            }

            $value = str_replace(array_keys($partsValues), array_values($partsValues), $pattern);
            $value = str_replace(array_keys($patternValuesToReplace), array_values($patternValuesToReplace), $value);
            $value = trim($value);
            $value = preg_replace('#\s+#', ' ', $value);
        }

        return $value;
    }

    /**
     * @param  string $def
     * @return array
     */
    protected function patternEvaluateParse($def)
    {
        $currentPos = 0;
        $length     = strlen($def);
        $parts      = array();

        while ($currentPos < $length) {
            preg_match('/(%s[0-9]{1,2})(.*?)(%s[0-9]{1,2})/', $def, $matches, 0, $currentPos);

            if (isset($matches[0])) {
                $parts[] = $matches[0];
                $currentPos += strlen($matches[1] . $matches[2]);
            } else {
                break;
            }
        }

        return $parts;
    }
}
