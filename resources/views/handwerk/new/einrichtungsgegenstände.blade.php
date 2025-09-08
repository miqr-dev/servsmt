@extends('layouts.admin_layout.admin_layout')

<style>
  .button-group {
    margin-right: 20px;
  }

  .item-selected {
    border: 1px solid #ced4da;
    /* Change this to the color you want for the border */
    padding: 10px;
    /* Adjust this to the amount of padding you want */
    border-radius: 5px;
    /* Added to make the border rounded */
    background-color: #f9f9f9;
    /* A light grey background for selected items */
  }

  .form-check-input {
    margin-top: 0.3em;
    margin-right: 0.3em;
  }

  .form-check-label {
    margin-right: 1em;
  }

  .form-check {
    margin-bottom: 0.5em;
  }

  /* Styling for custom checkbox */
  .form-check-input[type="checkbox"] {
    opacity: 0;
    position: absolute;
    margin: 0;
    z-index: -1;
    width: 0;
    height: 0;
    overflow: hidden;
    left: 0;
    pointer-events: none;
  }

  .form-check-input[type="checkbox"]+.form-check-label {
    position: relative;
    cursor: pointer;
    padding: 0;
  }

  .form-check-input[type="checkbox"]+.form-check-label:before {
    content: '';
    margin-right: 10px;
    display: inline-block;
    vertical-align: text-top;
    width: 20px;
    height: 20px;
    background: white;
    border: 1px solid #004873;
    border-radius: 4px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .1);
  }

  .form-check-input[type="checkbox"]:checked+.form-check-label:before {
    background: #004873;
    border-color: #004873;
  }

  .form-check-input[type="checkbox"]:checked+.form-check-label:after {
    content: '';
    position: absolute;
    left: 6px;
    top: 12px;
    background: white;
    width: 2px;
    height: 2px;
    box-shadow: 2px 0 0 white, 4px 0 0 white, 4px -2px 0 white, 4px -4px 0 white, 4px -6px 0 white, 4px -8px 0 white;
    transform: rotate(45deg);
  }
</style>

@section('content')
@include('tickets.layout_ticket.header',['title'=>'Einrichtungsgegenstände'])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <!-- Profile Image -->
        <div class="card card-secondary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{route ('form_store_handwerk')}}" method="post">
              @csrf
              <!-- child cards -->
              <div class="row mx-auto">
                <!-- Submitter Section layout_ticket submitter.blade.php -->
                @include('handwerk.layout.submitter')
                <!--end Submitter Section -->
                <!-- second card -->
                <div class="col-lg-8">
                  <div class="card card-secondary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Mobiliar - Einrichtung">
                      <div class="card-body box-profile form-group">
                        <div class="row">
                          <div class="form-group col-lg-6">
                            <label for="printer_place"> Standort &nbsp;<i class="fa-solid fa-hammer fa-lg"
                                style="color: #004873;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_place" name="location_id"
                              required>
                            </select>
                          </div>
                          <div class="form-group col-lg-6">
                            <label for="printer_room"> Raum &nbsp;<i class="fa-solid fa-hammer fa-lg"
                                style="color: #004873;"></i></label>
                            <select class="custom-select form-control mb-2" id="printer_room" name="room_id">
                            </select>
                            <div>
                              <p class="text-sm text-gray text-bold"><i class="fa-solid fa-circle-info fa-lg"></i>
                                Falls
                                der Raum nicht im Dropdown-Menü enthalten ist, verwenden Sie die
                                benutzerdefinierte Eingabe unten.</p>
                            </div>
                          </div>
                          <div class="form-group col-lg-6">
                          </div>
                          <div class="form-group col-lg-6">
                            <label for="custom_room"> Raum / Ort</label>
                            <input type="text" name="custom_room" class="form-control">
                          </div>
                        </div>
                        <div class="row my-4 p-2">
                          <div class="col-lg-12">
                            <div class="d-flex justify-content-start flex-wrap card-deck">
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#tafel-options">Tafeln</button>
                                <div class="collapse mt-2" id="tafel-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input name="schiebetafel" type="checkbox" class="form-check-input item-checkbox"
                                        data-has-quantity-input="true" id="schiebetafel">
                                      <label for="schiebetafel" class="form-check-label">Schiebetafel</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input name="schiebetafel_qty" type="number"
                                        class="form-control form-control-sm quantity-input" min="1"
                                        style="width:60px;display:none">
                                    </div>
                                  </div>
                                  <div>
                                    <div class="form-check d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox" name="whiteboard"
                                          data-has-quantity-input="true" id="whiteboard">
                                        <label class="form-check-label" for="whiteboard">Whiteboard</label>
                                      </div>
                                      <div class="flex-shrink-0">
                                        <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                          name="whiteboard_qty" min="1" style="width: 60px; display: none;">
                                      </div>
                                    </div>
                                  </div>
                                  <div>
                                    <div class="form-check d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox" name="kreidetafel"
                                          data-has-quantity-input="true" id="kreidetafel">
                                        <label class="form-check-label" for="kreidetafel">Kreidetafel</label>
                                      </div>
                                      <div class="flex-shrink-0">
                                        <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                          name="kreidetafel_qty" min="1" style="width: 60px; display: none;">
                                      </div>
                                    </div>
                                    <div class="form-check d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox" name="pinnwand"
                                          data-has-quantity-input="true" id="pinnwand">
                                        <label class="form-check-label" for="pinnwand">Pinnwand</label>
                                      </div>
                                      <div class="flex-shrink-0">
                                        <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                          name="pinnwand_qty" min="1" style="width: 60px; display: none;">
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#tisch-options">Tische</button>
                                <div class="collapse mt-2" id="tisch-options">
                                  <div class="form-check d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox"
                                          name="schreibtisch_TN" id="schreibtisch_TN" data-has-size-select="true"
                                          data-has-height-adjustable="true">
                                        <label class="form-check-label" for="schreibtisch_TN">Schreibtisch TN</label>
                                      </div>
                                    </div>
                                    <ul class="mt-2 dimension-list list-unstyled" style="display: none;">
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_TN_70x70" id="schreibtisch_TN_70x70">
                                            <label class="form-check-label" for="schreibtisch_TN_70x70">70 x 70</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_TN_70x70_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_TN_80x80" id="schreibtisch_TN_80x80">
                                            <label class="form-check-label" for="schreibtisch_TN_80x80">80 x 80</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_TN_80x80_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_TN_80x160" id="schreibtisch_TN_80x160">
                                            <label class="form-check-label" for="schreibtisch_TN_80x160">80 x
                                              160</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_TN_80x160_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                    </ul>
                                    <!-- ... previous code ... -->
                                  </div>
                                  <div class="form-check d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox"
                                          name="schreibtisch_DOZ" id="schreibtisch_DOZ" data-has-size-select="true"
                                          data-has-height-adjustable="true">
                                        <label class="form-check-label" for="schreibtisch_DOZ">Schreibtisch DOZ</label>
                                      </div>
                                    </div>
                                    <ul class="mt-2 dimension-list list-unstyled" style="display: none;">
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_DOZ_80x140" id="schreibtisch_DOZ_80x140">
                                            <label class="form-check-label" for="schreibtisch_DOZ_80x140">80 x
                                              140</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_DOZ_80x140_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_DOZ_80x160" id="schreibtisch_DOZ_80x160">
                                            <label class="form-check-label" for="schreibtisch_DOZ_80x160">80 x
                                              160</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_DOZ_80x160_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_DOZ_80x180" id="schreibtisch_DOZ_80x180">
                                            <label class="form-check-label" for="schreibtisch_DOZ_80x180">80 x
                                              180</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_DOZ_80x180_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                    </ul>
                                    <!-- ... previous code ... -->
                                  </div>
                                  <div class="form-check d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                      <div class="flex-grow-1">
                                        <input class="form-check-input item-checkbox" type="checkbox"
                                          name="schreibtisch_MA" id="schreibtisch_MA" data-has-size-select="true"
                                          data-has-height-adjustable="true">
                                        <label class="form-check-label" for="schreibtisch_MA">Schreibtisch MA</label>
                                      </div>
                                    </div>
                                    <ul class="mt-2 dimension-list list-unstyled" style="display: none;">
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_MA_80x140" id="schreibtisch_MA_80x140">
                                            <label class="form-check-label" for="schreibtisch_MA_80x140">80 x
                                              140</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_MA_80x140_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_MA_80x160" id="schreibtisch_MA_80x160">
                                            <label class="form-check-label" for="schreibtisch_MA_80x160">80 x
                                              160</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_MA_80x160_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                      <!-- More dimensions here ... -->
                                      <li>
                                        <div class="d-flex align-items-center">
                                          <div class="flex-grow-1">
                                            <input class="form-check-input dimension-checkbox" type="checkbox"
                                              name="schreibtisch_MA_80x180" id="schreibtisch_MA_80x180">
                                            <label class="form-check-label" for="schreibtisch_MA_80x180">80 x
                                              180</label>
                                          </div>
                                          <div class="flex-shrink-0">
                                            <input class="form-control form-control-sm ml-3 dimension-quantity"
                                              type="number" name="schreibtisch_MA_80x180_qty" min="1"
                                              style="width: 60px; display: none;">
                                          </div>
                                        </div>
                                      </li>
                                    </ul>
                                    <!-- ... previous code ... -->
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="stehtisch"
                                        id="stehtisch" data-has-quantity-input="true">
                                      <label class="form-check-label" for="stehtisch">Stehtisch</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="stehtisch_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="gesprächstisch_rund" id="gesprächstisch_rund"
                                        data-has-quantity-input="true">
                                      <label class="form-check-label" for="gesprächstisch_rund">Gesprächstisch
                                        rund</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="gesprächstisch_rund_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="konferenztisch" id="konferenztisch" data-has-quantity-input="true">
                                      <label class="form-check-label" for="konferenztisch">Konferenztisch</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="konferenztisch_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="couchtisch"
                                        id="couchtisch" data-has-quantity-input="true">
                                      <label class="form-check-label" for="couchtisch">Couchtisch</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="couchtisch_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="beistelltisch"
                                        id="beistelltisch" data-has-quantity-input="true">
                                      <label class="form-check-label" for="beistelltisch">Beistelltisch</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="beistelltisch_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#stuhl-options">Stühle</button>
                                <div class="collapse mt-2" id="stuhl-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        data-has-quantity-input="true" name="schreibtischstuhl" id="schreibtischstuhl">
                                      <label class="form-check-label" for="schreibtischstuhl">Schreibtischstuhl</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="schreibtischstuhl_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="bürostuhl"
                                        data-has-quantity-input="true" id="bürostuhl">
                                      <label class="form-check-label" for="bürostuhl">Bürostuhl</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="bürostuhl_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="stapelstühl"
                                        data-has-quantity-input="true" id="stapelstühl">
                                      <label class="form-check-label" for="stapelstühl">Stapelstühl</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="stapelstühl_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#schrank-options">Schränke</button>
                                <div class="collapse mt-2" id="schrank-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="rollcontainer"
                                        data-has-quantity-input="true" id="rollcontainer">
                                      <label class="form-check-label" for="rollcontainer">Rollcontainer</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="rollcontainer_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        data-has-quantity-input="true" name="standcontainer" id="standcontainer">
                                      <label class="form-check-label" for="standcontainer">Standcontainer</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="standcontainer_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="hochschrank"
                                        data-has-quantity-input="true" id="hochschrank">
                                      <label class="form-check-label" for="hochschrank">Hochschrank</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="hochschrank_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="ordnerhöhen_2"
                                        data-has-quantity-input="true" id="ordnerhöhen_2">
                                      <label class="form-check-label" for="ordnerhöhen_2">2 Oh Sideboard</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="ordnerhöhen_2_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="ordnerhöhen_3"
                                        data-has-quantity-input="true" id="ordnerhöhen_3">
                                      <label class="form-check-label" for="ordnerhöhen_3">3 Oh Sideboard</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="ordnerhöhen_3_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="hängeschrank"
                                        data-has-quantity-input="true" id="hängeschrank">
                                      <label class="form-check-label" for="hängeschrank">Hängeschrank</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="hängeschrank_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#vorhang-options">Sonnenschutz</button>
                                <div class="collapse mt-2" id="vorhang-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        data-has-quantity-input="true" name="lamellenvorhang" id="lamellenvorhang">
                                      <label class="form-check-label" for="lamellenvorhang">Lamellenvorhang</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="lamellenvorhang_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="rollo"
                                        data-has-quantity-input="true" id="rollo">
                                      <label class="form-check-label" for="rollo">Rollo</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="rollo_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#dekoration-options">Dekoration</button>
                                <div class="collapse mt-2" id="dekoration-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="bilder"
                                        data-has-quantity-input="true" id="bilder">
                                      <label class="form-check-label" for="bilder">Bilder</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="bilder_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#wc-options">WC</button>
                                <div class="collapse mt-2" id="wc-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="handtuchspender" data-has-quantity-input="true" id="handtuchspender">
                                      <label class="form-check-label" for="handtuchspender">Handtuchspender</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="handtuchspender_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="toilettenpapierhalter" data-has-quantity-input="true"
                                        id="toilettenpapierhalter">
                                      <label class="form-check-label"
                                        for="toilettenpapierhalter">Toilettenpapierhalter</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="toilettenpapierhalter_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="desinfektionsmittelspender" data-has-quantity-input="true"
                                        id="desinfektionsmittelspender">
                                      <label class="form-check-label"
                                        for="desinfektionsmittelspender">Desinfektionsmittelspender</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="desinfektionsmittelspender_qty" min="1"
                                        style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="button-group">
                                <button type="button" class="btn btn-secondary" data-toggle="collapse"
                                  data-target="#küche-options">Küche</button>
                                <div class="collapse mt-2" id="küche-options">
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="barzeile"
                                        data-has-quantity-input="true" id="barzeile">
                                      <label class="form-check-label" for="barzeile">Barzeile</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="barzeile_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox"
                                        name="bar_Hochstühle" data-has-quantity-input="true" id="bar_Hochstühle">
                                      <label class="form-check-label" for="bar_Hochstühle">Bar Hochstühle</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="bar_Hochstühle_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                  <div class="form-check d-flex align-items-center">
                                    <div class="flex-grow-1">
                                      <input class="form-check-input item-checkbox" type="checkbox" name="küchenzeile"
                                        data-has-quantity-input="true" id="küchenzeile">
                                      <label class="form-check-label" for="küchenzeile">Küchenzeile</label>
                                    </div>
                                    <div class="flex-shrink-0">
                                      <input class="form-control form-control-sm ml-3 quantity-input" type="number"
                                        name="küchenzeile_qty" min="1" style="width: 60px; display: none;">
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="notizen"> Notizen</label>
                          <textarea type="text" name="notizen" class="form-control notizen"></textarea>
                        </div>
                        <div>
                          <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div><!--end second card -->
              </div>
            </form>
          </div><!-- /.card-body -->
        </div><!-- /.card -->
      </div> <!-- /.col -->
    </div> <!-- /.row -->
  </div><!-- /.container-fluid -->
</section><!-- /.content -->
@endsection

@section('script')
<script>
  function resetDropdown(id, placeholder) {
    $(`#${id}`).empty().append(new Option(placeholder, ''));
  }


  function generateSelectOptions() {
    let options = '';
    for (let i = 0; i < 20; i++) {
      options += `<option value="${i}">${i}</option>`;
    }
    return options;
  }

  $(document).ready(function () {

    // Pre-populate select dropdowns with quantity options
    $('.quantity-select').html(generateSelectOptions());

    $('.item-checkbox').change(function () {
      var itemContainer = $(this).closest('.form-check');
      var qtyInput = itemContainer.find('.quantity-input');
      var dimensionList = itemContainer.find('.dimension-list');
      var heightAdjustableCheckbox = itemContainer.find('.height-check');
      var dimensionInput = itemContainer.find('.dimension-input');

      if ($(this).is(':checked')) {
        if ($(this).data('has-quantity-input')) {
          qtyInput.show().val('1');  // add this
        }
        if ($(this).data('has-size-select')) {
          dimensionInput.show();
        }
        if ($(this).data('has-height-adjustable')) {
          heightAdjustableCheckbox.show();
        }
        dimensionList.show();
        itemContainer.addClass('item-selected');
      } else {
        qtyInput.hide().val('');
        dimensionInput.hide().val('');
        heightAdjustableCheckbox.hide().prop('checked', false);
        dimensionList.hide().find('.dimension-checkbox').prop('checked', false);
        dimensionList.find('.dimension-quantity').hide().val('');
        itemContainer.removeClass('item-selected');
      }
    });

    $('.dimension-checkbox').change(function () {
      var listItem = $(this).closest('li');
      var qtyInput = listItem.find('.dimension-quantity');

      if ($(this).is(':checked')) {
        qtyInput.show().val('1');  // add this
      } else {
        qtyInput.hide().val('');
      }
    });

    // Prevent manual input of zero or negative values
    $('.quantity-input').on('input', function () {
      if ($(this).val() < 1) {
        $(this).val('1');
      }
    });


    let selectAddresslisten = [];
    let roomlisten = [];

    resetDropdown('printer_place', "Standort...");
    resetDropdown('printer_room', "Raum...");

    $.ajax({
      type: "get",
      url: "{{route('room_list')}}",
    }).done(function (data) {
      $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '" ></optgroup>');

      data['locations'].map((item) => {
        $(`#printer_place #${data.place.id}`).append(new Option(item.address, item.id));
        selectAddresslisten.push(item);
      });
    });

    $(document).on("change", "#printer_place", function () {
      resetDropdown('printer_room', "Raum...");

      selectAddresslisten.map((address) => {
        if (address.id == $(this).val()) {
          address.invrooms.map((room) => {
            $("#printer_room").append(new Option(room.rname + ' (' + room.altrname + ')', room.id));
            roomlisten.push(room);
          });
        }
      });
    });
    
    $('#submitter_standort_exception').on('change', function () {
      var newCity = $(this).val();
      var url = "{{ route('room_list') }}/" + newCity;

      $.ajax({
        type: "get",
        url: url,
      }).done(function (data) {
        // Empty the current dropdowns
        $("#printer_place").empty();
        $("#printer_room").empty();

        // Fill the printer_place dropdown with new data
        $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '" ></optgroup>');

        data['locations'].map((item) => {
          $(`#printer_place #${data.place.id}`).append(new Option(item.address, item.id));
          selectAddresslisten.push(item);
        });
      });
    });
  });
</script>
@endsection