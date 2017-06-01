<?php namespace App\Listeners\Frontend\Answer;
use App\Events\Frontend\Answer\Feedback;
use App\Logic\QuestionLogic;
use App\Models\Answer;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Feedback as FeedbackModel;

class AnswerEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param Feedback $event
     */
    public function feedback($event)
    {
        $feedback = FeedbackModel::find($event->feedback_id);
        $answer = Answer::find($feedback->source_id);
        $fields[] = [
            'title' => '回答内容',
            'value' => $answer->content
        ];
        $fields[] = [
            'title' => '评价内容',
            'value' => $feedback->content,
            'short' => true
        ];
        $fields[] = [
            'title' => '评价星数',
            'value' => $feedback->star,
            'short' => true
        ];

        QuestionLogic::slackMsg($answer->question,$fields)->send('用户['.$feedback->user->name.']评价了回答');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Feedback::class,
            'App\Listeners\Frontend\Answer\AnswerEventListener@feedback'
        );
    }
}
