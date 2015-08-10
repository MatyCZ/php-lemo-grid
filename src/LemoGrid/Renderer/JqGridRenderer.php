<?php

namespace LemoGrid\Renderer;

use LemoGrid\Event\RendererEvent;
use Zend\Json\Encoder as JsonEncoder;

class JqGridRenderer extends AbstractRenderer
{
    /**
     * Render data for JqGrid
     *
     * @return void
     */
    public function renderData()
    {
        $resultSet = $this->getGrid()->getPlatform()->getResultSet();
        $data = $resultSet->getData();
        $dataCount = count($data);

        $event = new RendererEvent();
        $event->setAdapter($this->getGrid()->getAdapter());
        $event->setGrid($this->getGrid());
        $event->setResultSet($this->getGrid()->getPlatform()->getResultSet());

        $this->getGrid()->getEventManager()->trigger(RendererEvent::EVENT_RENDER_DATA, $this, $event);

        $this->setGrid($event->getGrid());
        $this->getGrid()->setAdapter($event->getAdapter());
        $this->getGrid()->getPlatform()->setResultSet($event->getResultSet());

        $json = array(
            'page'    => $this->getGrid()->getPlatform()->getNumberOfCurrentPage(),
            'total'   => $this->getGrid()->getAdapter()->getNumberOfPages(),
            'records' => $this->getGrid()->getAdapter()->getCountOfItemsTotal(),
            'rows'    => array(),
        );

        for ($indexRow = 0; $indexRow < $dataCount; $indexRow++) {
            $json['rows'][] = array(
                'id'   => $indexRow +1,
                'cell' => $data[$indexRow]
            );
        }

        $userData = $this->getGrid()->getPlatform()->getResultSet()->getDataUser();
        if (!empty($userData)) {
            $json['userdata'] = $userData;
        }

        ob_clean();
        header('Content-Type: application/json');
        echo JsonEncoder::encode($json);
        exit;
    }
}