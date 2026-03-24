@php
    $badgeColors = new \Illuminate\Support\Collection(
        ['red', 'orange', 'amber', 'yellow',
        'green', 'emerald', 'teal', 'cyan',
        'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose']);
@endphp
<x-layouts.main-content title="Employees" heading="List of employees" subheading="Manage employees">
    <div class="flex gap-4 w-full flex-1 flex-col rounded-xl">
        <div>
            <form method="GET" action="{{ route('users.employees') }}">
                @csrf
                <div class="flex justify-between">
                    <div class="w-full flex flex-wrap gap-4">
                        <div>
                            <flux:input icon="magnifying-glass" clearable name="search" :value="$search ?? ''" placeholder="Search employees">
                                <x-slot name="iconTrailing">
                                    <flux:button square id="filled-regex-true" size="sm" variant="filled" class="-mr-2 font-mono {{ isset($searchWithRegex) && $searchWithRegex ? '' : 'hidden!' }}" onclick="document.getElementById('filter-regex').click()">.*</flux:button>
                                    <flux:button square id="filled-regex-false" size="sm" variant="subtle" class="-mr-2 font-mono {{ isset($searchWithRegex) && $searchWithRegex ? 'hidden!' : '' }}" onclick="document.getElementById('filter-regex').click()">.*</flux:button>
                                    <input type="checkbox" id="filter-regex" name="search-with-regex" value="1" {{ isset($searchWithRegex) && $searchWithRegex ? 'checked' : '' }} class="hidden" onclick="document.getElementById('filled-regex-true').classList.toggle('hidden!'); document.getElementById('filled-regex-false').classList.toggle('hidden!');"/>
                                </x-slot>
                            </flux:input>
                        </div>
                        <flux:dropdown align="center">
                            <flux:button icon="user" :variant="isset($filterByGender) ? 'filled' : 'subtle'">Gender</flux:button>
                            <flux:navmenu>
                                @foreach (['M' => 'Male', 'F' => 'Female'] as $gender => $label)
                                    <flux:menu.checkbox onclick="document.getElementById('filter-gender-{{ $gender }}').click()" :checked="in_array($gender, $filterByGender ?? [])">
                                        {{ $label }}
                                    </flux:menu.checkbox>
                                    <input type="checkbox" id="filter-gender-{{ $gender }}" name="gender[]" value="{{ $gender }}" {{ in_array($gender, $filterByGender ?? []) ? 'checked' : '' }} class="hidden">
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:dropdown align="center">
                            <flux:button icon="information-circle" :variant="isset($filterByStatus) ? 'filled' : 'subtle'">Status</flux:button>
                            <flux:navmenu>
                                @foreach (['active', 'blocked'] as $status)
                                    <flux:menu.checkbox onclick="document.getElementById('filter-status-{{ $status }}').click()" :checked="in_array($status, $filterByStatus ?? [])">
                                        {{ ucwords($status) }}
                                    </flux:menu.checkbox>
                                    <input type="checkbox" id="filter-status-{{ $status }}" name="status[]" value="{{ $status }}" {{ in_array($status, $filterByStatus ?? []) ? 'checked' : '' }} class="hidden">
                                @endforeach
                            </flux:navmenu>
                        </flux:dropdown>
                    </div>
                    <div class="grow-0 flex flex-row space-y-3 justify-start gap-4">
                        <flux:dropdown align="center">
                            <flux:button icon="funnel" :variant="isset($orderByElement) || ($orderByDirection ?? 'desc') != 'desc' ? 'filled' : 'subtle'">Order By</flux:button>
                            <flux:navmenu>
                                <flux:menu.radio.group>
                                    @foreach (['created_at', 'name', 'email', 'gender'] as $orderBy)
                                        <flux:menu.radio onclick="document.getElementById('order-element-{{ $orderBy }}').click()" :checked="($orderByElement ?? 'created_at') == $orderBy">
                                            {{ ucwords(str_replace('_', ' ', $orderBy)) }}
                                        </flux:menu.radio>
                                        <input type="radio" id="order-element-{{ $orderBy }}" name="order-element" value="{{ $orderBy != 'created_at' ? $orderBy: '' }}" {{ ($orderByElement ?? 'created_at') == $orderBy ? 'checked' : '' }} class="hidden">
                                    @endforeach
                                </flux:menu.radio.group>
                                <flux:radio.group variant="segmented" size="sm" class="mt-2">
                                    <flux:radio onclick="document.getElementById('order-direction-asc').click()" :checked="($orderByDirection ?? 'desc') == 'asc'">
                                        <flux:tooltip content="Ascending" position="bottom">
                                            <flux:icon icon="bars-arrow-down" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-asc" name="order-direction" value="asc" {{ ($orderByDirection ?? 'desc') == 'asc' ? 'checked' : '' }} class="hidden">
                                    <flux:radio onclick="document.getElementById('order-direction-desc').click()" :checked="($orderByDirection ?? 'desc') == 'desc'">
                                        <flux:tooltip content="Descending" position="bottom">
                                            <flux:icon icon="bars-arrow-up" />
                                        </flux:tooltip>
                                    </flux:radio>
                                    <input type="radio" id="order-direction-desc" name="order-direction" value="desc" {{ ($orderByDirection ?? 'desc') == 'desc' ? 'checked' : '' }} class="hidden">
                                </flux:radio.group>
                            </flux:navmenu>
                        </flux:dropdown>
                        <flux:button.group>
                            <flux:button variant="primary" type="submit" class="cursor-pointer w-28">Filter</flux:button>
                            <flux:button variant="outline" :href="route('users.employees')" class="w-10"><flux:icon.x-mark/></flux:button>
                        </flux:button.group>
                    </div>
                </div>
            </form>
        </div>
        <div class="flex justify-start">
            <div class="w-full">
                <div class="static sm:absolute top-8 right-8 flex flex-wrap justify-start sm:justify-end items-center gap-4">
                    <flux:button.group>
                        <flux:button variant="primary" icon="plus" :href="route('users.employees.register')">Register a new employee</flux:button>
                    </flux:button.group>
                </div>

                <div class="font-base text-sm text-gray-700 dark:text-gray-300">
                    <table class="w-full table-auto border-collapse">
                        <colgroup>
                            <col class="w-0">
                            <col class="w-2/6">
                            <col class="w-2/6">
                            <col class="w-1/6">
                            <col class="w-1/6">
                            <col class="w-0">
                        </colgroup>
                        <thead>
                            <tr class="bg-zinc-100 dark:bg-zinc-700 text-gray-800 dark:text-gray-200 font-semibold rounded-lg shadow-xs ring-1 ring-zinc-200 dark:ring-zinc-600">
                                <th class="px-2 py-2 text-left rounded-l-lg" colspan="2">Name</th>
                                <th class="px-2 py-2 text-left">Email</th>
                                <th class="px-2 py-1 text-left">Gender</th>
                                <th class="px-2 py-1 text-left">Status</th>
                                <th class="rounded-r-lg"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                            <tr class="border-b border-b-zinc-100 dark:border-b-zinc-700">
                                <td class="px-2 py-2 text-left">
                                    <flux:avatar size="xs" src="{{ $employee->photo_url }}" />
                                </td>
                                <td class="px-2 py-2 text-left">
                                    {{ $employee->name }}
                                </td>
                                <td class="px-2 py-2 text-left">
                                    <span class="inline-flex items-center gap-2">
                                        <flux:link href="mailto:{{ $employee->email }}">{{ $employee->email }}</flux:link>
                                        @if ($employee->email_verified_at)
                                            <flux:tooltip content="This email is verified">
                                                <flux:icon variant="micro" name="check" class="text-green-500 dark:text-green-400" />
                                            </flux:tooltip>
                                        @else
                                            <flux:tooltip content="This email is not verified">
                                                <flux:icon variant="micro" name="x-mark" class="text-red-500 dark:text-red-400" />
                                            </flux:tooltip>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-left">
                                    @if ($employee->gender == 'M')
                                        <flux:badge variant="pill" size="sm" color="cyan">Male</flux:badge>
                                    @elseif ($employee->gender == 'F')
                                        <flux:badge variant="pill" size="sm" color="pink">Female</flux:badge>
                                    @else
                                        <flux:badge variant="pill" size="sm">Other</flux:badge>
                                    @endif
                                </td>
                                 <td class="px-2 py-2 text-left">
                                    @if ($employee->blocked)
                                        <flux:badge variant="pill" size="sm" color="red">Blocked</flux:badge>
                                    @else
                                        <flux:badge variant="pill" size="sm" color="green">Active</flux:badge>
                                    @endif
                                </td>
                                <td class="text-left">
                                    @if ($employee->blocked)
                                        <flux:modal :name="'unblock-employee-'.$employee->id" class="min-w-[22rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading size="lg" class="flex gap-2 mr-8">
                                                        <flux:icon name="lock-open"/>
                                                        Unblock employee "{{ $employee->name }}"?
                                                    </flux:heading>
                                                    <flux:text class="mt-2">
                                                        <p>You're about to unblock this employee.</p>
                                                    </flux:text>
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" id="form-unblock-employee-{{ $employee->id }}" class="flex items-center">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="primary">Unblock</flux:button>
                                                    </form>
                                                </div>
                                            </div>
                                        </flux:modal>
                                    @else
                                        <flux:modal :name="'demote-employee-member-'.$employee->id" class="min-w-[22rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading size="lg" class="flex gap-2 mr-8">
                                                        <flux:icon name="user"/>    
                                                        Demote "{{ $employee->name }}" to Member?
                                                    </flux:heading>
                                                    <flux:text class="mt-2">
                                                        <p>You're about to demote this employee to the rank of member.</p>
                                                    </flux:text>
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" id="form-demote-employee-{{ $employee->id }}" class="flex items-center">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="primary">Demote</flux:button>
                                                    </form>
                                                </div>
                                            </div>
                                        </flux:modal>
                                        <flux:modal :name="'promote-employee-board-'.$employee->id" class="min-w-[22rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading size="lg" class="flex gap-2 mr-8">
                                                        <flux:icon name="briefcase"/>    
                                                        Promote "{{ $employee->name }}" to Board?
                                                    </flux:heading>
                                                    <flux:text class="mt-2">
                                                        <p>You're about to promote this employee to the rank of board.</p>
                                                    </flux:text>
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" id="form-promote-employee-board-{{ $employee->id }}" class="flex items-center">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="primary">Promote</flux:button>
                                                    </form>
                                                </div>
                                            </div>
                                        </flux:modal>
                                        <flux:modal :name="'block-employee-'.$employee->id" class="min-w-[22rem]">
                                            <div class="space-y-6">
                                                <div>
                                                    <flux:heading size="lg" class="flex gap-2 mr-8">
                                                        <flux:icon name="no-symbol"/>
                                                        Block employee "{{ $employee->name }}"?
                                                    </flux:heading>
                                                    <flux:text class="mt-2">
                                                        <p>You're about to block this employee.</p>
                                                    </flux:text>
                                                </div>
                                                <div class="flex gap-2">
                                                    <flux:spacer />
                                                    <flux:modal.close>
                                                        <flux:button variant="ghost">Cancel</flux:button>
                                                    </flux:modal.close>
                                                    <form method="POST" id="form-block-employee-{{ $employee->id }}" class="flex items-center">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" variant="danger">Block</flux:button>
                                                    </form>
                                                </div>
                                            </div>
                                        </flux:modal>
                                    @endif
                                    <flux:modal :name="'delete-employee-'.$employee->id" class="min-w-[22rem]">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg" class="flex gap-2 mr-8">
                                                    <flux:icon name="trash"/>
                                                    Delete "{{ $employee->name }}"'s account?</flux:heading>
                                                <flux:text class="mt-2">
                                                    <p>You're about to delete this employee's account.</p>
                                                    <p>This action cannot be reverted!</p>
                                                </flux:text>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">Cancel</flux:button>
                                                </flux:modal.close>
                                                <form method="POST" action="{{ route('users.delete', ['user' => $employee->id]) }}" class="flex items-center">
                                                    @csrf
                                                    @method('DELETE')
                                                    <flux:button type="submit" variant="danger">Delete</flux:button>
                                                </form>
                                            </div>
                                        </div>
                                    </flux:modal>
                                    <flux:dropdown class="w-48" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"/>
                                        <flux:menu>
                                            <flux:menu.item icon="eye" :href="route('users.view', ['user' => $employee])">View</flux:menu.item>
                                            @if ($employee->id != auth()->user()->id)
                                                <flux:menu.item icon="pencil-square" class="cursor-pointer" :href="route('users.edit', ['user' => $employee])">Edit</flux:menu.item>
                                                <flux:modal.trigger :name="'delete-employee-'.$employee->id">
                                                    <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">Delete</flux:menu.item>
                                                </flux:modal.trigger>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="border-b-zinc-400 dark:border-b-zinc-500 text-gray-600 dark:text-gray-400">
                                <td class="px-2 py-2 text-left rounded-b-lg" colspan="6">
                                    <x-pagination :paginator="$employees" paginatorName="employees"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.main-content>
