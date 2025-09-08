@extends('layouts.admin_layout.admin_layout')

@section('content')
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="form-row col-md-6">
          <div class="form-group col-md-6">
            <select name="section" id="section" class="form-control">
              <option value="">Filter by Sections...</option>
              @foreach ($section as $key => $value) 
              <option value="{{$key}}">{{$value}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-6">
            <select name="area" id="area" class="form-control">
              <option value="">Filter by Bereich...</option>
              @foreach ($area as $key => $value) 
              <option value="{{$key}}">{{$value}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <hr>
        <table id="dataTable" class="display" style="width:100%">
          <thead>
              <tr>
                  <th>IP Adresse</th>
                  <th>section</th>
                  <th>area</th>
                  <th>Name</th>
                  <th>Beschreibung</th>
                  <th>Benutzername</th>
                  <th>Passwort</th>
                  <th>Anmerkungen</th>
              </tr>
          </thead>
          <tbody>
            @foreach($matrix as $mat)
              <tr>
                  <td>{{$mat->ip_address}}</td>
                  <td>{{$mat->section}}</td>
                  <td>{{$mat->area}}</td>
                  <td>{{$mat->name}}</td>
                  <td>{{$mat->description}}</td>
                  <td>{{$mat->username}}</td>
                  <td>{{$mat->password}}</td>
                  <td>{{$mat->notes}}</td>
              </tr>
            @endforeach 
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

@endsection



@section('script')
<script>

const $doc = $(document);
let $dataTable = $("#dataTable");
let $dropdownInput = $("select.form-control");
let $section = $("#section");
let $area = $("#area");
let $clear = $("#clear");
let $keyup = $.Event("keyup", { keyCode: 13 });

$doc.ready(function() {
  $dataTable.DataTable({
    order: [[2, 'asc'], [1, 'asc']],
    "paging": false,
    rowGroup: {
        dataSrc: [ 2, 1 ]
    },
    columnDefs: [{
        targets: [ 1, 2 ],
        visible: false
    }],
    order: [[1, "asc"]] 
  });


// Dropdown filters
$dropdownInput.change(function() {
  $sectionVal = $section.find(":selected").text(); 
  $areaVal = $area.find(":selected").text(); // Find category value
  $("#searchSection")
    .val($sectionVal)
    .trigger($keyup); // Inject into column search
  $("#searchArea")
    .val($areaVal)
    .trigger($keyup); // Inject into column search
});
	// Clear button for dropdown filters and search
	$clear.click(function() {
		$('.form-control:not([name="dataTable_length"])')
			.val("")
			.trigger($keyup); // Clear all inputs except the # of entries
	});
	
	// Remove BS small modifier
	$('select[name="dataTable_length"]').removeClass('input-sm');
	$('#dataTable_filter input').removeClass('input-sm');

  
  	// Add a hidden text input to each footer cell
	$("#dataTable thead th").each(function() {
		var $title = $(this).text().trim();
		$(this).html('<div class="form-group"><label>Search ' + $title + ':<br/><input class="form-control" id="search' + $title + '" type="hidden"/></label></div>');
	});
	// Apply the search functionality to hidden inputs
	$dataTable
		.DataTable()
		.columns()
		.every(function() {
			var $that = this;
			$("input", this.header()).on("keyup change", function() {
				if ($that.search() !== this.value) {
					$that.search(this.value, false, true, false).draw(); // strict search
				}
			});
    });
    
});
</script>

@endsection