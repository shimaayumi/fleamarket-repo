<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>商品詳細 - {{ $purchase->item->item_name }}</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>


    <header>
        <div class="header">
            <div class="header__inner">
                <a class="header__logo" href="/">
                    <img src="{{ asset('images/logo.svg') }}" alt="ロゴ" />
                </a>
            </div>
        </div>
    </header>

    @php
    $isSelfSeller = Auth::id() === $purchase->item->user_id;
    $chatPartner = $isSelfSeller ? $purchase->user : $purchase->item->user;
    $chatPartnerImage = optional(optional($chatPartner->profile)->profile_image)->filename ?? null;
    $userName = $chatPartner->name;
    @endphp

    <div class="sidebar">
        <h3 class="sidebar-title">その他の取引</h3>
        <ul class="transaction-list">
            @foreach($tradingItems as $item)
            <li class="{{ $item->id === $purchase->id ? 'active' : '' }}">
                <a href="{{ route('chat.show', ['purchase' => $item->id]) }}">
                    <div class="transaction-item">
                        <div class="transaction-name">{{ $item->item->item_name }}</div>
                    </div>
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="chat-area">


        <div class="chat-header">
            @php
            $chatPartnerImage = optional($chatPartner->profile)->profile_image;
            $path = 'profiles/' . $chatPartnerImage;
            @endphp

            @if ($chatPartnerImage && Storage::disk('public')->exists($path))
            <img src="{{ asset('storage/' . $path) }}"
                alt="{{ $chatPartner->name }}のプロフィール画像"
                class="profile-image profile-image--seller">
            @else
            <div class="profile-image profile-image--seller fallback">
                {{ mb_substr($chatPartner->name, 0, 1) }}
            </div>
            @endif



            <span class="chat-partner-name">{{ $userName }} さんとの取引画面</span>

            @php
            $isSeller = Auth::id() === $purchase->item->user_id;
            $isBuyer = Auth::id() === $purchase->user_id;

            $alreadyReviewed = \App\Models\Review::where('purchase_id', $purchase->id)
            ->where('reviewer_id', Auth::id())
            ->exists();

            $buyerReviewed = \App\Models\Review::where('purchase_id', $purchase->id)
            ->where('reviewer_id', $purchase->user_id)
            ->exists();
            @endphp

            {{-- 評価して取引完了ボタン（モーダル起動） --}}
            @if ($isBuyer && !$alreadyReviewed)
            <button type="button" class="btn-complete" data-bs-toggle="modal" data-bs-target="#completeModal{{ $purchase->id }}">
                取引を完了する
            </button>
            @endif



            <!-- 評価モーダル -->
            <div class="modal fade" id="completeModal{{ $purchase->id }}" tabindex="-1" aria-labelledby="completeModalLabel{{ $purchase->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mx-auto">
                    <form action="{{ route('chat.complete', ['purchase' => $purchase->id]) }}" method="POST">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">取引が完了しました</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">今回の取引相手はどうでしたか？</label>
                                <div class="stars" data-selected="0">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span class="star" data-value="{{ $i }}">&#9733;</span>
                                        @endfor
                                </div>
                                <input type="hidden" name="rating" id="rating-value" value="">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn-submit-review">送信する</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($isSeller && !$alreadyReviewed && $buyerReviewed && $purchase->status === 'in_progress')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const modalElement = document.getElementById('completeModal{{ $purchase->id }}');
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    }
                });
            </script>
            @endif
        </div>

        <div class="product-info">
            @php
            $firstImage = optional($purchase->item->images->first())->item_image;
            @endphp

            @if ($firstImage && Storage::exists('public/images/' . $firstImage))
            <img src="{{ asset('storage/images/' . $firstImage) }}"
                alt="{{ $purchase->item->item_name }}"
                class="product-image">
            @else
            <div class="product-image fallback">画像なし</div>
            @endif

            <div class="product-details">
                <p class="product-name">{{ $purchase->item->item_name }}</p>
                <p class="product-price">¥{{ number_format($purchase->item->price) }}</p>
            </div>
        </div>

        <div class="chat-messages">
            @foreach($messages as $message)
            @php
            $msgProfile = optional($message->user->profile);
            $msgProfileImage = optional($msgProfile)->profile_image;
            $isOwn = $message->user_id === Auth::id();
            @endphp

            <div class="chat-message {{ $isOwn ? 'right' : 'left' }}">
                <div class="chat-user-info">
                    <span class="chat-username">{{ $message->user->name }}</span>
                    @if ($msgProfileImage)
                    <img src="{{ asset('storage/profiles/' . $msgProfileImage) }}" class="chat-profile-image">
                    @else
                    <div class="chat-profile-image fallback">
                        {{ mb_substr($message->user->name, 0, 1) }}
                    </div>
                    @endif

                </div>

                @if ($message->content)
                <div class="chat-bubble">
                    <p>{{ $message->content }}</p>
                </div>
                @endif

                @if ($message->image_path)
                <div class="chat-image-wrapper">
                    <img src="{{ asset('storage/chat_images/' . $message->image_path) }}" class="chat-image" alt="チャット画像">
                </div>
                @endif

                @if ($isOwn)
                <div class="chat-actions">
                    <button onclick="editMessage({{ $message->id }}, @js($message->content))" class="chat-action-button">編集</button>
                    <form action="{{ route('chat.message.destroy', $message) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="chat-delete-button" onclick="return confirm('本当に削除しますか？')">削除</button>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                <li style="color:red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>

        @endif
     

        <form action="{{ route('chat.message.store', ['purchase' => $purchase->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <textarea id="chat-message" name="message" rows="4" cols="40" placeholder="取引メッセージを記入してください">{{ old('message') }}</textarea>
            <label for="image-upload" class="image-upload-button" title="画像を追加">
                <span class="upload-label">画像を追加</span>
            </label>
            <input type="file" name="image" id="image-upload" accept=".jpeg,.jpg,.png" style="display:none;" />
            <button type="submit" class="send-button"><i class="fas fa-paper-plane"></i></button>
        </form>

        <div class="modal fade" id="editMessageModal" tabindex="-1" aria-labelledby="editMessageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered mx-auto">
                <form id="editMessageForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header modal-header-update">
                            <h5 class="modal-title">メッセージを編集</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body modal-body-update">
                            <textarea name="content" id="editMessageContent" class="form-control textarea-edit" rows="4"></textarea>
                            <input type="hidden" id="editMessageId">
                        </div>
                        <div class="modal-footer modal-footer-update">
                            <button type="submit" class="btn btn-primary">更新</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        function editMessage(id, content) {
            document.getElementById('editMessageId').value = id;
            document.getElementById('editMessageContent').value = content;
            document.getElementById('editMessageForm').action = `/chat/message/${id}`;
            const modal = new bootstrap.Modal(document.getElementById('editMessageModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.stars .star');
            const ratingInput = document.getElementById('rating-value');
            stars.forEach((star, index) => {
                star.addEventListener('mouseover', () => highlightStars(index));
                star.addEventListener('mouseout', resetStars);
                star.addEventListener('click', () => selectStars(index + 1));
            });

            function highlightStars(index) {
                stars.forEach((s, i) => s.classList.toggle('hovered', i <= index));
            }

            function resetStars() {
                stars.forEach(s => s.classList.remove('hovered'));
            }

            function selectStars(value) {
                ratingInput.value = value;
                stars.forEach((s, i) => s.classList.toggle('selected', i < value));
            }

            const textarea = document.getElementById('chat-message');
            const storageKey = 'chat_message_draft_{{ $purchase->id }}';
            const saved = localStorage.getItem(storageKey);
            if (saved) textarea.value = saved;

            textarea.addEventListener('input', () => {
                localStorage.setItem(storageKey, textarea.value);
            });

            const form = textarea.closest('form');
            form.addEventListener('submit', () => {
                localStorage.removeItem(storageKey);
            });
        });
    </script>
</body>

</html>