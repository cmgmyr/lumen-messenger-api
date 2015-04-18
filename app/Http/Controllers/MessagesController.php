<?php namespace App\Http\Controllers;

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
     * @var ThreadTransformer
     */
    protected $transformer;

    /**
     * @var User
     */
    protected $user;

    /**
     * Very simple API authentication. You should implement something
     * a lot better than this...
     *
     * @param ThreadTransformer $transformer
     */
    public function __construct(ThreadTransformer $transformer)
    {
        $api_key = Input::get('api_key');
        $this->user = User::where('api_key', $api_key)->firstOrFail();
        Auth::login($this->user);

        $this->transformer = $transformer;
    }

    /**
     * Show all of the message threads associated with the user
     *
     * Example URL: /api/messages?api_key=30ce6864e2589b01bc002b03aa6a7923&per_page=10&page=2
     *
     * @return mixed
     */
    public function index()
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

        $data = $this->transformer->transformCollection($threads->toArray()['data']);

        $response = [
            'pagination' => $this->buildPagination($threads),
            'data'       => $data
        ];

        return $this->respondWithSuccess($response);
    }

    /**
     * Shows a message thread
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Session::flash('error_message', 'The thread with ID: ' . $id . ' was not found.');

            return redirect('messages');
        }

        // show current user in list if not a current participant
        // $users = User::whereNotIn('id', $thread->participantsUserIds())->get();

        // don't show the current user in list
        $userId = Auth::user()->id;
        $users = User::whereNotIn('id', $thread->participantsUserIds($userId))->get();

        $thread->markAsRead($userId);

        return view('messenger.show', compact('thread', 'users'));
    }

    /**
     * Creates a new message thread
     *
     * @return mixed
     */
    public function create()
    {
        $users = User::where('id', '!=', Auth::id())->get();

        return view('messenger.create', compact('users'));
    }

    /**
     * Stores a new message thread
     *
     * @return mixed
     */
    public function store()
    {
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

        return redirect('messages');
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
