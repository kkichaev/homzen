@if (theme_option('real_estate_use_location_in_search_box_as_dropdown', 'no') === 'yes')
    @php
        $cities = \Botble\Location\Models\City::query()
            ->wherePublished()
            ->select('name', 'id')
            ->orderBy('name')
            ->get();
    @endphp

    <div class="form-group-2 form-style">
        <label>{{ __('Location') }}</label>
        <div class="position-relative">
            <div class="group-select">
                <select name="city_id" id="location" class="select_js">
                    <option value="">{{ __('All') }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->getKey() }}"@selected(request()->query('city_id') == $city->getKey())>{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
@else
    <div class="form-group-2 form-style" data-bb-toggle="search-suggestion">
        <label>{{ __('Location') }}</label>
        <div class="position-relative">
            <div @class(['group-ip', 'ip-icon' => $style === 3])>
                <input type="text" class="form-control" placeholder="{{ __('Search for Location') }}" value="{{ BaseHelper::stringify(request()->query('location')) }}" name="location" data-url="{{ route('public.ajax.cities') }}" />
                <x-core::icon name="ti ti-current-location" @class(['icon-right icon-location' => $style === 3]) />
            </div>
            <div data-bb-toggle="data-suggestion"></div>
        </div>
    </div>
@endif
