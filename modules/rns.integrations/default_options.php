<?php
return [
  'files' => [
    'fileMaxSize' => 3,
    'taskMaxCount' => 300
  ],
    'email' => [
        'regexpTitle' => 'title=([^#]+)',
        'regexpProject' => 'project=([^#]+)',
        'regexpEndDate' => 'endDate=(\d{2}\.\d{2}\.\d{4})',
        'regexpPriority' => 'priority=([^#]+)',
        'regexpTag' => 'tags=([^#]+)',
        'beginMarker' => '#',
        'endMarker' => '##',
        'acceptComment' => 'Автоматически сформированное согласие на участие',
        'refuseComment' => 'Автоматически сформированная причина отказа',
        'errorMessageEntity' => 'Задача не создана. Обратитесь в службу поддержки.',
        'errorMessageComment' => 'Ответ на комментарий не создан. Обратитесь в службу поддержки.',
        'taskIdTemplate' => '\#TASK_ID:\s+(?<taskId>\d+)\#',
        'commentIdTemplate' => '\#COMMENT_ID:\s+(?<commentId>\d+)\#',
        'subjectAcceptedTemplate' => 'Accepted:\s*.+\[(?<eventId>\d+)\]',
        'subjectDeclinedTemplate' => 'Declined:\s*.+\[(?<eventId>\d+)\]'
    ]
];
