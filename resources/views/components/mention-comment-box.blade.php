{{-- @mention Comment Box Component --}}
{{-- Usage: @include('components.mention-comment-box', ['action' => route(...), 'placeholder' => '...']) --}}

<div class="mention-comment-wrapper">
    <form action="{{ $action }}" method="POST" class="discussion-form">
        @csrf
        <div class="mention-input-wrapper">
            <textarea name="body" id="comment-body-{{ $id ?? 'default' }}" class="form-control mention-textarea"
                placeholder="{{ $placeholder ?? 'Tulis komentar... (Gunakan @ untuk mention user)' }}" rows="3"
                required></textarea>
            <div class="mention-dropdown" id="mention-dropdown-{{ $id ?? 'default' }}"></div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i>
            Kirim
        </button>
    </form>
</div>

<style>
    .mention-comment-wrapper {
        position: relative;
    }

    .mention-input-wrapper {
        position: relative;
        flex: 1;
    }

    .mention-textarea {
        width: 100%;
        min-height: 80px;
        resize: vertical;
    }

    .mention-dropdown {
        position: absolute;
        bottom: 100%;
        left: 0;
        right: 0;
        max-height: 200px;
        overflow-y: auto;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: none;
        margin-bottom: 4px;
    }

    .mention-dropdown.active {
        display: block;
    }

    .mention-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background 0.15s;
    }

    .mention-item:hover,
    .mention-item.selected {
        background: #f1f5f9;
    }

    .mention-item-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .mention-item-info {
        flex: 1;
    }

    .mention-item-name {
        font-weight: 500;
        color: #1e293b;
        font-size: 0.875rem;
    }

    .mention-item-email {
        color: #94a3b8;
        font-size: 0.75rem;
    }

    .mention-no-results {
        padding: 1rem;
        text-align: center;
        color: #94a3b8;
        font-size: 0.875rem;
    }
</style>

<script>
    (function () {
        const textareaId = 'comment-body-{{ $id ?? "default" }}';
        const dropdownId = 'mention-dropdown-{{ $id ?? "default" }}';
        const textarea = document.getElementById(textareaId);
        const dropdown = document.getElementById(dropdownId);

        if (!textarea || !dropdown) return;

        let mentionStart = -1;
        let selectedIndex = 0;
        let users = [];

        textarea.addEventListener('input', async function (e) {
            const value = textarea.value;
            const cursorPos = textarea.selectionStart;

            // Find @ symbol before cursor
            const beforeCursor = value.substring(0, cursorPos);
            const atIndex = beforeCursor.lastIndexOf('@');

            if (atIndex === -1 || (atIndex > 0 && beforeCursor[atIndex - 1] !== ' ' && beforeCursor[atIndex - 1] !== '\n')) {
                hideDropdown();
                return;
            }

            // Check if there's already a completed mention (with brackets)
            const afterAt = beforeCursor.substring(atIndex);
            if (afterAt.includes(']') || afterAt.includes(')')) {
                hideDropdown();
                return;
            }

            mentionStart = atIndex;
            const searchQuery = beforeCursor.substring(atIndex + 1);

            // Don't search if query has space (completed word)
            if (searchQuery.includes(' ') || searchQuery.includes('\n')) {
                hideDropdown();
                return;
            }

            // Fetch users - filter by project if project_id is provided
            try {
                const projectId = '{{ $projectId ?? '' }}';
                const apiUrl = projectId
                    ? `/api/users/search?q=${encodeURIComponent(searchQuery)}&project_id=${projectId}`
                    : `/api/users/search?q=${encodeURIComponent(searchQuery)}`;
                const response = await fetch(apiUrl);
                users = await response.json();

                if (users.length > 0) {
                    showDropdown(users);
                } else {
                    dropdown.innerHTML = '<div class="mention-no-results">Tidak ada user ditemukan</div>';
                    dropdown.classList.add('active');
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                hideDropdown();
            }
        });

        textarea.addEventListener('keydown', function (e) {
            if (!dropdown.classList.contains('active')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, users.length - 1);
                updateSelection();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelection();
            } else if (e.key === 'Enter' && users.length > 0) {
                e.preventDefault();
                selectUser(users[selectedIndex]);
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        textarea.addEventListener('blur', function () {
            // Delay hide to allow click on dropdown
            setTimeout(() => hideDropdown(), 200);
        });

        function showDropdown(users) {
            selectedIndex = 0;
            dropdown.innerHTML = users.map((user, index) => `
            <div class="mention-item ${index === 0 ? 'selected' : ''}" data-index="${index}" data-id="${user.id}" data-name="${user.name}">
                <div class="mention-item-avatar">${user.initials}</div>
                <div class="mention-item-info">
                    <div class="mention-item-name">${user.name}</div>
                    <div class="mention-item-email">${user.email}</div>
                </div>
            </div>
        `).join('');

            // Add click handlers
            dropdown.querySelectorAll('.mention-item').forEach(item => {
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    const index = parseInt(this.dataset.index);
                    selectUser(users[index]);
                });
            });

            dropdown.classList.add('active');
        }

        function hideDropdown() {
            dropdown.classList.remove('active');
            mentionStart = -1;
            users = [];
        }

        function updateSelection() {
            dropdown.querySelectorAll('.mention-item').forEach((item, index) => {
                item.classList.toggle('selected', index === selectedIndex);
            });
        }

        function selectUser(user) {
            const value = textarea.value;
            const beforeMention = value.substring(0, mentionStart);
            const afterCursor = value.substring(textarea.selectionStart);

            // Insert mention format: @[User Name](user_id)
            const mention = `@[${user.name}](${user.id}) `;
            textarea.value = beforeMention + mention + afterCursor;

            // Set cursor after mention
            const newPos = mentionStart + mention.length;
            textarea.setSelectionRange(newPos, newPos);
            textarea.focus();

            hideDropdown();
        }
    })();
</script>