@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header">
        <h5>{{ $page_title }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Role Name</label>
                <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
            </div>

            <h5 class="mt-3">Assign Permissions</h5>

            @php
                $groupedPermissions = [];
                foreach ($permissions as $permission) {
                    $parts = explode('_', $permission->name);
                    if(count($parts) > 1) {
                        $group = array_pop($parts); // last part as group
                        $permLabel = implode(' ', $parts); // remaining parts as label
                    } else {
                        $group = 'general';
                        $permLabel = $permission->name;
                    }

                    $groupedPermissions[$group][] = [
                        'name' => $permission->name,
                        'label' => $permLabel
                    ];
                }
            @endphp

            @foreach($groupedPermissions as $group => $perms)
                <div class="card mt-2">
                    <div class="card-header bg-light text-dark">
                        <strong>{{ ucfirst($group) }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($perms as $perm)
                                <div class="col-md-3">
                                    <label>
                                        <input type="checkbox" name="permissions[]" value="{{ $perm['name'] }}"
                                            @if($role->hasPermissionTo($perm['name']))
                                                checked
                                            @endif
                                        >
                                        {{ $perm['label'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn--primary mt-3">Update</button>
        </form>
    </div>
</div>
@endsection