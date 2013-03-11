<?php

namespace LemoGrid\View\Helper;

use LemoGrid\Exception;
use LemoGrid\GridInterface;
use Zend\View\Helper\AbstractHelper;

class Grid extends AbstractHelper
{
    /**
     * @var GridInterface
     */
    protected $grid;

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  GridInterface|null $grid
     * @return string|Grid
     */
    public function __invoke(GridInterface $grid = null)
    {
        if (!$grid) {
            return $this;
        }

        if(null !== $grid) {
            $this->setGrid($grid);
        }

        return $this->render();
    }

    public function render()
    {
        if (null === $this->grid) {
            throw new Exception\UnexpectedValueException('No instance of LemoGrid\GridInterface given');
        }

        if (!$this->grid instanceof GridInterface) {
            throw new Exception\UnexpectedValueException(sprintf(
                'Expected instance of LemoGrid\GridInterface; received "%s"',
                get_class($this->grid)
            ));
        }

        try {
            $html = array();

            return implode(PHP_EOL, $html);
        } catch (\Exception $e) {
            ob_clean();
            trigger_error($e->getMessage(), E_USER_WARNING);

            return $e->getMessage();
        }
    }

    protected function prepareGrid()
    {

    }

    protected function prepareColumn()
    {

    }

    /**
     * Set instance of Grid
     *
     * @param  GridInterface $grid
     * @return Grid
     */
    public function setGrid(GridInterface $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Retrieve instance of Grid
     *
     * @throws Exception\UnexpectedValueException
     * @return GridInterface
     */
    public function getGrid()
    {
        return $this->grid;
    }
}
