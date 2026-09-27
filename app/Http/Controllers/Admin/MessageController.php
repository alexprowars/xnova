<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Access;
use App\Admin\Presentation;
use App\Engine\Enums\MessageType;
use App\Engine\Messages\MessageFactory;
use App\Models\Message;
use App\Models\User;
use App\Notifications\SystemMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class MessageController extends AdminController
{
	public function index(Request $request)
	{
		Access::authorize('messages');

		$columns = ['id', 'user', 'from_id', 'type', 'subject', 'message', 'date'];

		return $this->listing(
			$request,
			'messages',
			Message::query()->with('user'),
			$columns,
			[
				Presentation::field('user_id'),
				Presentation::field(
					'type',
					'select',
					array_map(
						fn(MessageType $type) => [
							'value' => $type->value,
							'label' => $type->getLabel(),
						],
						MessageType::cases(),
					),
				),
			],
			function (Message $message) {
				return [
					...Presentation::record($message, ['from_id', 'type', 'subject', 'date']),
					'user' => Presentation::userLink($message->user),
					'message' => ['preview_url' => '/admin/messages/' . $message->id],
				];
			},
			false,
		);
	}

	public function show(Message $message)
	{
		Access::authorize('messages');

		$subject = $message->subject ? __($message->subject) : null;

		try {
			$renderer = MessageFactory::get($message->message);

			$subject = $subject ?: $renderer?->getSubject();
			$text = $renderer?->render() ?? '';
		} catch (Throwable $exception) {
			report($exception);
			return response()->json(['message' => __('admin.message_load_failed')], 422);
		}

		return response()->json([
			'subject' => strip_tags($subject ?? ''),
			'text' => $text,
		]);
	}

	public function destroy(Message $message)
	{
		Access::authorize('messages');

		$message->delete();

		return back()->with('admin_notice', __('admin.saved'));
	}

	public function mailing()
	{
		Access::authorize('mailing');

		return Inertia::render('Admin/Edit', [
			'resource' => 'messages',
			'title' => __('admin.mailing'),
			'fields' => [Presentation::field('subject'), Presentation::field('message', 'textarea')],
			'url' => '/admin/messages/mailing',
			'tables' => [],
			'links' => [],
			'confirm' => true,
		]);
	}

	public function sendMailing(Request $request)
	{
		Access::authorize('mailing');

		$data = $request->validate([
			'subject' => 'required|string|max:100',
			'message' => 'required|string|max:50000',
		]);

		User::query()
			->chunkById(200, function ($users) use ($data) {
				foreach ($users as $user) {
					$user->notify(
						new SystemMessage(MessageType::System, e($data['message']), strip_tags($data['subject'])),
					);
				}
			});

		return back()->with('admin_notice', __('admin.sent'));
	}
}
