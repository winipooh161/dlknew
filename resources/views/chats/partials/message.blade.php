<div class="chat-message">
    <strong>{{ $message->user->name }}</strong>
    <p>{{ $message->content }}</p> 
    <span>{{ $message->created_at->format('d.m.Y H:i') }}</span>
</div>
