<div class="p-16 space-y-8">
    @livewire('database-notifications')

    <input placeholder="Search..." type="search" wire:model.live="search" />

    <table>
        <thead class="border-b">
            <tr>
                <th class="px-3 py-2 text-left">Name</th>
                <th class="px-3 py-2 text-left">Email address</th>
                <th class="px-3 py-2 text-left">Role</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            @forelse ($users as $user)
                <tr>
                    <td class="px-3 py-1">{{ $user->name }}</td>
                    <td class="px-3 py-1">{{ $user->email }}</td>
                    <td class="px-3 py-1">{{ $user->pivot->role }}</td>
                </tr>
            @empty
                <tr>
                    <td class="px-3 py-1" colspan="3">No users found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="max-w-3xl">
        {{ $users->links() }}
    </div>

    <button wire:click="export" type="button">
        Export
    </button>
</div>
