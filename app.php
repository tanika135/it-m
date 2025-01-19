<?php

class App
{
    protected int $taskID = 0;

    public function __construct(int $taskId)
    {
        $this->taskID = $taskId;
    }

    public function run()
    {
        $task = $this->getTask();

        $crmConnections = $task['result']['task']['ufCrmTask'];

        $contactId = $this->getContactIdFromTask($crmConnections);

        if ($contactId <= 0) {
            echo "No contact";
            die();
        }

        $deals = $this->GetDeals($contactId);

        $lastDealId = (int) $deals['result'][array_key_first($deals['result'])]['ID'];

        if ($lastDealId <= 0) {
            echo "No deal";
            die();
        }

        $crmConnections[] = 'D_'.$lastDealId;

        $this->updateTask($crmConnections);
    }

    /**
     * @param array $crmConnections
     * @return int
     */
    protected function getContactIdFromTask(array $crmConnections) : int
    {
        $contactId = 0;

        foreach ($crmConnections as $connection) {
            $type = substr($connection, 0, 2);
            if ($type == 'C_') {
                $contactId = (int) str_replace('C_', '', $connection);
            }
        }

        return $contactId;
    }

    /**
     * @return array
     */
    protected function getTask () : array
    {
        return CRest::call(
            'tasks.task.get',
            [
                'taskId' =>  $this->taskID,
                'select' => ['UF_CRM_TASK']
            ]
        );
    }

    /**
     * @param int $contactId
     * @return array
     */
    protected function GetDeals (int $contactId) : array
    {
        return CRest::call(
            'crm.deal.list',
            [
                'filter' =>  ['CONTACT_ID' =>  $contactId],
                'order' => ['ID' => 'DESC']
            ]
        );
    }

    /**
     * @param $crmConnections
     * @return array
     */
    protected function updateTask($crmConnections) : array
    {
        return CRest::call(
            'tasks.task.update',
            [
                'taskId' => $this->taskID,
                'fields' => [
                    'UF_CRM_TASK' => $crmConnections
                ]
            ]
        );
    }
}