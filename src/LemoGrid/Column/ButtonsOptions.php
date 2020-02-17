<?php

namespace LemoGrid\Column;

use Exception;
use Laminas\Stdlib\AbstractOptions;

class ButtonsOptions extends AbstractOptions
{
    /**
     * @var Button[]
     */
    protected $buttons = [];

    /**
     * @var string
     */
    protected $separator = '&nbsp;';

    /**
     * @param  array|Button[] $buttons
     * @throws Exception
     * @return ButtonsOptions
     */
    public function setButtons(array $buttons)
    {
        foreach ($buttons as $button) {
            if ($button instanceof Button) {
                $btn = $button;
            } else {
                $type       = isset($button['type']) ? ucfirst(strtolower($button['type'])) : null;
                $name       = isset($button['name']) ? $button['name'] : null;
                $options    = isset($button['options']) ? $button['options'] : null;
                $attributes = isset($button['attributes']) ? $button['attributes'] : null;
                $conditions = isset($button['conditions']) ? $button['conditions'] : null;
                $class = 'LemoGrid\Column\\' . $type;

                if (!in_array($type, ['Button', 'Buttonlink', 'Route'])) {
                    throw new Exception('Button type must be Button, ButtonLink or Route');
                }
                $btn = new $class($name, $options, $attributes, $conditions);
            }

            $this->buttons[] = $btn;
        }

        return $this;
    }

    /**
     * @return Button[]
     */
    public function getButtons()
    {
        return $this->buttons;
    }

    /**
     * @return ButtonsOptions
     */
    public function clearButtons()
    {
        $this->buttons = [];

        return $this;
    }

    /**
     * @param  string $separator
     * @return ButtonsOptions
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
}
