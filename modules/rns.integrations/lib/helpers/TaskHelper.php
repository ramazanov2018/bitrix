<?php

namespace RNS\Integrations\Helpers;

class TaskHelper
{
    /**
     * Добавляет комментарий к задаче.
     * @param int $taskId
     * @param string $comment
     * @param array $files
     * @return int
     * @throws \Bitrix\Main\ObjectException
     * @throws \CTaskAssertException
     * @throws \TasksException
     * @throws \Exception
     */
    public static function addComment(int $taskId, string $comment, array $files = [])
    {
        global $USER;

        $taskItem = \CTaskItem::getInstance($taskId, $USER->getID());

        if (!$taskItem) {
            throw new \Exception("Задача с таким ID не найдена: " . $taskId);
        }

        $data = [
          'AUTHOR_ID ' => $USER->getID(),
          'USE_SMILES' => 'N',
          'POST_MESSAGE' => $comment,
          'AUX' => 'Y'
        ];
        if (!empty($files)) {
            $data['FILES'] = $files;
        }

        return \CTaskCommentItem::add($taskItem, $data);
    }
}
