<?php

namespace Lemo\Grid\Column;

use Exception;
use Laminas\Stdlib\AbstractOptions;

class ButtonsOptions extends AbstractOptions
{
    /**
     * @var Button[]
     */
    protected array $buttons = [];

    /**
     * @var string
     */
    protected string $separator = '&nbsp;';

    /**
     * @param  array|Button[] $buttons
     * @throws Exception
     * @return self
     */
    public function setButtons(array $buttons): self
    {
        foreach ($buttons as $button) {
            if ($button instanceof Button) {
                $btn = $button;
            } else {
                $type       = $button['type'] ?? null;
                $name       = $button['name'] ?? null;
                $options    = $button['options'] ?? null;
                $attributes = $button['attributes'] ?? null;
                $conditions = $button['conditions'] ?? null;

                if (false !== strpos($type, 'Lemo\Grid\Column')) {
                    if (!in_array($type, [Button::class, ButtonLink::class, Route::class])) {
                        throw new Exception('Button type must be Button, ButtonLink or Route');
                    }

                    $btn = new $type($name, $options, $attributes, $conditions);
                } else {
                    $type = ucfirst(strtolower($type));

                    if (!in_array($type, ['Button', 'Buttonlink', 'Route'])) {
                        throw new Exception('Button type must be Button, ButtonLink or Route');
                    }

                    $class = 'Lemo\Grid\Column\\' . $type;

                    $btn = new $class($name, $options, $attributes, $conditions);
                }

            }

            $this->buttons[] = $btn;
        }

        return $this;
    }

    /**
     * @return Button[]
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    /**
     * @return self
     */
    public function clearButtons(): self
    {
        $this->buttons = [];

        return $this;
    }

    /**
     * @param  string $separator
     * @return self
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }
}
