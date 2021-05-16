<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        MAPBOX Maps
                    </div>
                    <div class="card-body">
                        <div wire:ignore id='map' style='width: 100%; height: 75vh;'></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        MAPBOX Maps
                    </div>
                    <div class="card-body">
                        <form @if ($idEdit)
                            wire:submit.prevent="updateLocation"
                        @else
                            wire:submit.prevent="saveLocation"
                        @endif>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Longtitude</label>
                                        <input wire:model="long" type="text" class="form-control">
                                        @error('long')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Lattitude</label>
                                        <input wire:model="lat" type="text" class="form-control">
                                        @error('lat')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label>Title</label>
                                <input wire:model="title" type="text" class="form-control">
                                @error('title')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea wire:model="description" class="form-control"></textarea>
                                @error('description')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Picture</label>
                                <div class="custom-file">
                                    <input wire:model="image" type="file" class="custom-file-input" id="customFile">
                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                </div>
                                @if ($image)
                                    <img src="{{$image->temporaryUrl()}}" class="img-fluid">
                                @endif
                                @if ($imageUrl && !$image)
                                    <img src="{{asset('/storage/images/'.$imageUrl)}}" class="img-fluid">
                                    
                                @endif
                                @error('image')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-dark text-white btn-block">{{$idEdit ? "Update Location" : "Save Location"}}</button>
                                @if ($idEdit)
                                    <button wire:click="deleteLocation" type="button" class="btn btn-danger text-white btn-block">Delete Location</button>
                                @endif
                            </div>
                        </form>                   
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    //memasukan livewire
    document.addEventListener('livewire:load',() =>{
        const defaultLocation   = [105.91935657756147  , -6.374864360311847]; //Membuat variabel Default Location


        mapboxgl.accessToken = '{{ env("MAPBOX_KEY") }}';
        var map = new mapboxgl.Map({        
        container: 'map',
        center: defaultLocation,
        zoom : 11.15,
        style: 'mapbox://styles/mapbox/streets-v11'
        });

         // menampilkan data geoJson
    const loadLocations = (geoJson) => 
    {
            geoJson.features.forEach((location) => {
                const {geometry, properties} = location
                const {iconSize, locationId, title, image, description} = properties

                let markerElement = document.createElement('div')
                markerElement.className = 'marker' + locationId
                markerElement.id = locationId
                markerElement.style.backgroundImage = 'url(https://docs.mapbox.com/help/demos/custom-markers-gl-js/mapbox-icon.png)'
                markerElement.style.backgroundSize = 'cover'
                markerElement.style.width = '50px'
                markerElement.style.height = '50px'

                const imageStorage = '{{asset("/storage/images")}}' + '/' + image

                //style popup
                const content= `<div style="overflow-y, auto;max-height:400px,width:100%">
                    <table class="table table-sm mt-2">
                        <tbody>
                            <tr>
                                <td>title</td>
                                <td>${title}</td>
                            </tr>
                            <tr>
                                <td>picture</td>
                                <td><img src="${imageStorage}" loading="lazy" class="img-fluid"></td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>${description}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>`

                markerElement.addEventListener('click',(e) =>{
                    const locationId = e.toElement.id
                    @this.findLocationById(locationId)
                })

                        //menampilkan popup
                        const popUp = new mapboxgl.Popup({
                            offset:25
                        }).setHTML(content).setMaxWidth("300px")


                        new mapboxgl.Marker(markerElement)
                        .setLngLat(geometry.coordinates)
                        .setPopup(popUp)
                        .addTo(map)
                    })
                }
                //menampilkan data
                    loadLocations({!! $geoJson !!})
                    //Create Location
                    window.addEventListener('locationAdded', (e) =>
                    {
                        loadLocations(JSON.parse(e.detail))
                    })
                        // Update Location
                        window.addEventListener('updateLocation', (e) =>{
                        loadLocations(JSON.parse(e.detail))
                        $('.mapboxgl-popup').remove()
                    })

                     // Delete Location
                    window.addEventListener('deleteLocation', (e) =>{
                        $('.marker' + e.detail).remove()
                        $('.mapboxgl-popup').remove()
                        
                    })
                    


        // mengubah style / tampilan maps
        // const style ="dark-v10"
        //light-v10, outdoors-v11, satellite-v9, streets-v11, dark-v10

        // map.setStyle(`mapbox://styles/mapbox/${style}`)

        //membuat control zoom dan minus pojok kanan atas
        map.addControl(new mapboxgl.NavigationControl())

        map.on('click', (e) => {
            const longtitude        = e.lngLat.lng //membuat variabel untuk menentukan titik kordinal
            const lattitude         = e.lngLat.lat //membuat variabel untuk menentukan titik kordinal

            @this.long = longtitude
            @this.lat = lattitude

            // console.log(longtitude, lattitude);
        })
    })
</script>
@endpush
