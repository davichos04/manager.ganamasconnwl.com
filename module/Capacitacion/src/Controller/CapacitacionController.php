<?php
declare(strict_types=1);

namespace Capacitacion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Capacitacion\Model\Table\CapacitacionTable;
use Capacitacion\Form\CapacitacionForm;

final class CapacitacionController extends AbstractActionController
{
    public function __construct(
        private CapacitacionTable $capTable,
        private CapacitacionForm $capForm
    ) {}

    public function indexAction(): ViewModel
    {
        return new ViewModel([
            'rows' => $this->capTable->fetchAll(),
        ]);
    }

    public function addAction()
    {
        $form = $this->capForm;

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data['published'] = isset($data['published']) ? 1 : 0;
            $data['created'] = date('Y-m-d H:i:s');
            $data['created_by'] = 0;

            $form->setData($data);
            if ($form->isValid()) {
                $id = $this->capTable->save($form->getData());
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $id]);
            }
        }

        return new ViewModel(['form' => $form]);
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id <= 0) return $this->redirect()->toRoute('capacitacion-admin/cap', ['action'=>'index']);

        $row = $this->capTable->get($id);
        $form = $this->capForm;
        $form->setData($row);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data['id'] = $id;
            $data['published'] = isset($data['published']) ? 1 : 0;
            $data['modified'] = date('Y-m-d H:i:s');
            $data['modified_by'] = 0;

            $form->setData(array_merge($row, $data));
            if ($form->isValid()) {
                $d = $form->getData();
                unset($d['created'], $d['created_by']);
                $this->capTable->save($d, $id);
                return $this->redirect()->toRoute('capacitacion-admin/builder', ['id' => $id]);
            }
        }

        return new ViewModel(['form' => $form, 'row' => $row]);
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if ($id > 0) $this->capTable->delete($id);
        return $this->redirect()->toRoute('capacitacion-admin/cap', ['action'=>'index']);
    }
}
