@extends('admin.menu_builder.layout')
@section('menu-content')
    <div class="container py-3">
        <h5 class="mb-3">Nested Menu View</h5>

        <ul class="menu-list ps-0">
            {{-- Menu Level 1 --}}
            @foreach ($category->where('parent_id', null) as $menu1)
                <li class="menu-item">
                    <strong>{{ $menu1->name }}</strong> (Level 1)

                    @php
                        $menu2Items = $category->where('parent_id', $menu1->id);
                    @endphp

                    @if ($menu2Items->count())
                        <ul class="submenu-list ps-3">
                            {{-- Menu Level 2 --}}
                            @foreach ($menu2Items as $menu2)
                                <li class="menu-item">
                                    <strong>{{ $menu2->name }}</strong> (Level 2)

                                    @php
                                        $menu3Items = $category->where('parent_id', $menu2->id);
                                    @endphp

                                    @if ($menu3Items->count())
                                        <ul class="submenu-list ps-3">
                                            {{-- Menu Level 3 --}}
                                            @foreach ($menu3Items as $menu3)
                                                <li class="menu-item">
                                                    <strong>{{ $menu3->name }}</strong> (Level 3)
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                </li>
                            @endforeach
                        </ul>
                    @endif

                </li>
            @endforeach
        </ul>
    </div>
@endsection

@push('style')
    <style>
        .menu-list, .submenu-list {
            list-style: none;
        }

        .menu-item {
            background: #f4f4f4;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 6px;
            border-radius: 5px;
        }

        .submenu-list {
            margin-top: 8px;
        }
    </style>
@endpush
