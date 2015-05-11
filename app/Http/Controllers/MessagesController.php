<?php namespace App\Http\Controllers;

use App\Services\Transformer\MessageTransformer;
use App\Services\Transformer\ThreadTransformer;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Thread;
use Cmgmyr\Messenger\Models\Message;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class MessagesController extends ApiController
{

    /**
     * @var User
     */
    protected $user;

    /**
     * Very simple API authentication. You should implement something
     * a lot better than this...
     */
    public function __construct()
    {
        $api_key = Input::get('api_key');
        $this->user = User::where('api_key', $api_key)->firstOrFail();
        Auth::login($this->user);
    }

    /**
     * Show all of the message threads associated with the user
     *
     * Example URL: GET /api/v1/messages?api_key=30ce6864e2589b01bc002b03aa6a7923&per_page=10&page=2
     *
     * @param ThreadTransformer $threadTransformer
     * @return mixed
     */
    public function index(ThreadTransformer $threadTransformer)
    {
        $userId = $this->user->id;
        $perPage = (int)Input::get('per_page', 25);

        // get all of the paginated threads
        $threads = Thread::forUser($userId)->latest('updated_at')->paginate($perPage);
        $threads->setPath(route('messages'));
        $threads->addQuery('api_key', $this->user->api_key);
        $threads->addQuery('per_page', $perPage);

        if ($threads->total() == 0) {
            return $this->respondNotFound('No results returned. Please check back later.');
        }

        // see if the threads have been read by the user
        $threads->each(function($thread) use ($userId) {
            $thread->is_unread = $thread->isUnread($userId);
        });

        $data = $threadTransformer->transformCollection($threads->toArray()['data']);

        $response = [
            'pagination' => $this->buildPagination($threads),
            'data'       => $data
        ];

        return $this->respondWithSuccess($response);
    }

    /**
     * Shows a message thread
     *
     * Example URL: GET /api/v1/messages/1?api_key=30ce6864e2589b01bc002b03aa6a7923
     *
     * @param MessageTransformer $messageTransformer
     * @param $id
     * @return mixed
     */
    public function show(MessageTransformer $messageTransformer, $id)
    {
        $userId = $this->user->id;

        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->respondNotFound('Sorry, the message thread was not found.');
        }

        $thread->markAsRead($userId);
        $messageData = $thread->toArray();

        // Get messages
        $messages = $thread->messages()->with('user')->get();
        $messageData['messages'] = $messages->toArray();

        // Get participants
        $participants = User::whereIn('id', $thread->participantsUserIds())->get();
        $messageData['participants'] = $participants->toArray();

        // Get non-participants
        $nonParticipants = User::whereNotIn('id', $thread->participantsUserIds())->get();
        $messageData['non_participants'] = $nonParticipants->toArray();

        $data = $messageTransformer->transform($messageData);

        $response = [
            'data' => $data
        ];

        return $this->respondWithSuccess($response);
    }

    /**
     * Stores a new message thread
     *
     * Example URL: POST /api/v1/messages?api_key=30ce6864e2589b01bc002b03aa6a7923
     *
     * @return mixed
     */
    public function store()
    {
        // @todo: run validation

        $input = Input::all();

        $thread = Thread::create(
            [
                'subject' => $input['subject'],
            ]
        );

        // Message
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id,
                'body'      => $input['message'],
            ]
        );

        // Sender
        Participant::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id,
                'last_read' => new Carbon
            ]
        );

        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipants($input['recipients']);
        }

        return $this->respondWithCreated([
            'id' => $thread->id
        ]);
    }

    /**
     * Adds a new message to a current thread
     *
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Session::flash('error_message', 'The thread with ID: ' . $id . ' was not found.');

            return redirect('messages');
        }

        $thread->activateAllParticipants();

        // Message
        Message::create(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::id(),
                'body'      => Input::get('message'),
            ]
        );

        // Add replier as a participant
        $participant = Participant::firstOrCreate(
            [
                'thread_id' => $thread->id,
                'user_id'   => Auth::user()->id
            ]
        );
        $participant->last_read = new Carbon;
        $participant->save();

        // Recipients
        if (Input::has('recipients')) {
            $thread->addParticipants(Input::get('recipients'));
        }

        return redirect('messages/' . $id);
    }
}
