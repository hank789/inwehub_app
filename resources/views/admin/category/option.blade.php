@foreach(load_categories($type,$root,$last??false) as $category)
    @if($select_id == $category->id)
        <option value="{{ $category->id }}" selected >{{ $category->name }}</option>
    @else
        <option value="{{ $category->id }}">{{ $category->name }}</option>
    @endif
@endforeach