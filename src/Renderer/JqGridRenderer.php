<?php

namespace Lemo\Grid\Renderer;

use Lemo\Grid\Event\RendererEvent;

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

        $json = [
            'page'    => $this->getGrid()->getPlatform()->getNumberOfCurrentPage(),
            'total'   => $this->getGrid()->getAdapter()->getNumberOfPages(),
            'records' => $this->getGrid()->getAdapter()->getCountOfItemsTotal(),
            'rows'    => [],
        ];

        for ($indexRow = 0; $indexRow < $dataCount; $indexRow++) {
            if (!empty($data[$indexRow]['rowId'])) {
                $rowId = $data[$indexRow]['rowId'];
            } else {
                $rowId = $indexRow + 1;
            }
            $json['rows'][] = [
                'id'   => $rowId,
                'cell' => $data[$indexRow]
            ];
        }

        $userData = $this->getGrid()->getPlatform()->getResultSet()->getDataUser();
        if (!empty($userData)) {
            $json['userdata'] = $userData;
        }

        ob_clean();
        header('Content-Type: application/json');
        echo json_encode($json);
        exit;
    }
}