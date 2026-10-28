@extends('admin.layouts.app')

@section('panel')
<div class="card">
    <div class="card-header">
        <h5>{{ $page_title }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Role Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <h5 class="mt-3">Assign Permissions</h5>
            
            @php
                $groupedPermissions = [];
                foreach ($permissions as $permission) {
                    // Explode permission by '_'
                    $parts = explode('_', $permission->name);
                    if(count($parts) > 1) {
                        $group = array_pop($parts); // last part is group (e.g., customers)
                        $permLabel = implode('_', $parts); // remaining parts as label
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
                                        <input type="checkbox" name="permissions[]" value="{{ $perm['name'] }}">
                                        {{ $perm['label'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn--primary mt-3">Save</button>
        </form>
    </div>
</div>
@endsection
