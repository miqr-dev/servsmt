@extends('layouts.admin_layout.admin_layout')

@section('content')
@include('tickets.layout_ticket.header', [
'title' => 'Onlinemarketing',
'colorClass' => 'ticket_header_korso',
'buttonClass' => 'btn-outline-green'
])

<section class="content">
  <div class="container-fluid col-lg-12">
    <div class="row">
      <div class="col-12 mx-auto">
        <div class="card card-thirdary card-outline">
          <div class="card-body box-profile form-group">
            <form action="{{ route('form_store_korso') }}" method="post" enctype="multipart/form-data" id="onlinemarketing-form">
              @csrf
              <div class="row mx-auto">
                @include('korso.layout.submitter')
                <div class="col-lg-10">
                  <div class="card card-thirdary card-outline">
                    <div id="underform">
                      <input type="hidden" name="problem_type" value="Onlinemarketing">
                      <input type="hidden" name="is_chatgpt_project" id="is_chatgpt_project" value="0">
                      <div class="card-body box-profile form-group">
                        <div class="form-group">
                          <label for="onlinemarketing_item">Was brauchen Sie ?</label>
                          <div class="row">
                            @foreach($onlinemarketingItems->chunk(15) as $chunk)
                            <div class="col-md-6">
                              @foreach($chunk as $item)
                              <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="item{{ $item->id }}" name="onlinemarketing_item"
                                  value="{{ $item->id }}" class="custom-control-input">
                                <label class="custom-control-label" for="item{{ $item->id }}">
                                  {{ $item->name }}
                                  @if($item->name === 'Fehlermeldung Chat GPT')
                                  <small class="text-muted d-block">bitte Accountname und Screenshot mitsenden</small>
                                  @endif
                                </label>
                              </div>
                              @if($item->name === 'Zugang Chat GPT')
                              <div class="chatgpt-project-link-wrap mb-4 ml-4">
                                <button type="button" class="btn btn-outline-info btn-sm" id="open-chatgpt-project-modal">
                                  ChatGPT-Projektvorschläge
                                </button>
                                <small class="d-block text-muted mt-2">Öffnet ein Lastenheft im Stil von MS Forms.</small>
                                <div id="chatgpt-project-status" class="small text-success font-weight-bold mt-2 d-none">
                                  Formular ausgefüllt.
                                </div>
                              </div>
                              @endif
                              @endforeach
                            </div>
                            @endforeach
                          </div>
                        </div>

                        <div class="form-group">
                          <label for="notizen">Notizen</label>
                          <textarea name="notizen" class="form-control summernote"></textarea>
                        </div>
                        <div class="form-group">
                          <label for="attachments" class="font-weight-bold">Anhänge <small class="text-muted">(Bilder,
                              PDFs, Office-Dateien)</small></label>

                          <div class="custom-file">
                            <input type="file" name="attachments[]" id="attachments" class="custom-file-input" multiple
                              accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                            <label class="custom-file-label" for="attachments">Dateien auswählen...</label>
                          </div>

                          <small class="form-text text-muted mt-1">Maximale Größe: 5MB pro Datei</small>
                          <div id="file-preview" class="d-flex flex-wrap mt-3 border rounded p-2 bg-light"></div>
                        </div>
                        <button type="submit" class="btn btn-outline-success col-lg-2 float-right">Einreichen</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal fade chatgpt-project-modal" id="chatgptProjectModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
                  <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header text-white">
                      <div>
                        <div class="small text-uppercase mb-1">MS Forms Style</div>
                        <h4 class="modal-title mb-0">KI-Assistent (CustomGPT) Lastenheft</h4>
                      </div>
                    </div>
                    <div class="modal-body px-0 py-0">
                      <div class="chatgpt-form-shell">
                        <div class="chatgpt-form-banner"></div>
                        <div class="chatgpt-form-body">
                          <div class="chatgpt-form-intro mb-4">
                            <h3 class="mb-2">Projektvorschlag</h3>
                            <p class="mb-0 text-muted">Bitte füllen Sie alle Abschnitte für Ihren gewünschten KI-Assistenten aus.</p>
                          </div>

                          <div class="chatgpt-form-section">
                            <div class="chatgpt-form-section-title">1. Use Case</div>
                            <label class="chatgpt-form-label required-field">Definiere deinen Anwendungsfall in 3-4 Wörtern („Projektname“)</label>
                            <textarea class="form-control ms-forms-input" name="chatgpt_project_name" rows="3"></textarea>
                          </div>

                          <div class="chatgpt-form-section">
                            <div class="chatgpt-form-section-title">2. Ziele der KI-Einführung</div>
                            <label class="chatgpt-form-label required-field">Gründe für die Einführung des KI-Assistenten</label>
                            <div class="chatgpt-form-card">
                              <div class="chatgpt-form-card-title">Engpässe und “Breaking Points” im bestehenden Prozess (Anlass der Einführung)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_introduction_reason" rows="5"></textarea>
                            </div>
                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Ziele die mit der Einführung des KI-Assistenten verfolgt werden</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_goal" rows="5"></textarea>
                            </div>
                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Kurzbeschreibung der einzelnen Schritte (Deine “Vorstellung”)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_process_steps" rows="5"></textarea>
                            </div>
                          </div>

                          <div class="chatgpt-form-section">
                            <div class="chatgpt-form-section-title">3. Beschreibung des Prozesses</div>
                            <p class="font-weight-bold mb-3">IST-Zustand und vorhandenes Material</p>
                            <p class="text-muted">Wichtig: Je mehr Beispiele bzw. konkrete Vorstellungen des Ergebnisses Du hast, desto besser wird der KI-Assistent.</p>

                            <div class="table-responsive">
                              <table class="table table-bordered ms-forms-table">
                                <tbody>
                                  <tr>
                                    <td>Existiert für den Use Case bereits ein Prozess?</td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="process_no" name="chatgpt_has_existing_process" value="0">
                                        <label class="custom-control-label" for="process_no">Nein</label>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="process_yes" name="chatgpt_has_existing_process" value="1">
                                        <label class="custom-control-label" for="process_yes">Ja</label>
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Gibt es Beispiele für ein perfektes Ergebnis?</td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="output_no" name="chatgpt_has_output_examples" value="0">
                                        <label class="custom-control-label" for="output_no">Nein</label>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="output_yes" name="chatgpt_has_output_examples" value="1">
                                        <label class="custom-control-label" for="output_yes">Ja (unten einfügen)</label>
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Gibt es eine Knowledge Base / gespeichertes Wissen?</td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="knowledge_no" name="chatgpt_has_knowledge_base" value="0">
                                        <label class="custom-control-label" for="knowledge_no">Nein</label>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="custom-control custom-radio">
                                        <input class="custom-control-input" type="radio" id="knowledge_yes" name="chatgpt_has_knowledge_base" value="1">
                                        <label class="custom-control-label" for="knowledge_yes">Ja (unten einfügen)</label>
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>

                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Beispiele für den perfekten Output (falls vorhanden)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_output_examples" rows="6"></textarea>
                            </div>

                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Vorhandenes Wissen / Knowledge Bases (falls vorhanden)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_knowledge_base" rows="6"></textarea>
                            </div>
                          </div>

                          <div class="chatgpt-form-section">
                            <div class="chatgpt-form-section-title">4. Sonstige Anforderungen</div>
                            <label class="chatgpt-form-label">Ergänzende Anforderungen an den KI-Assistenten</label>
                            <div class="chatgpt-form-card">
                              <div class="chatgpt-form-card-title">Sonstige Anforderungen</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_additional_requirements" rows="8"></textarea>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                      <small class="text-muted">Das Modal wird nur über „Cancel“ geschlossen.</small>
                      <div>
                        <button type="button" class="btn btn-outline-secondary" id="cancel-chatgpt-project">Cancel</button>
                        <button type="button" class="btn btn-info" id="save-chatgpt-project">Formular übernehmen</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@section('script')
<style>
  .chatgpt-project-modal .modal-header {
    background: linear-gradient(135deg, #0f9fb3, #2563eb);
    border-bottom: none;
  }
  .chatgpt-form-shell {
    background: #eef6fb;
    min-height: 100%;
  }
  .chatgpt-form-banner {
    height: 120px;
    background: linear-gradient(135deg, #159fb4, #2457d6);
  }
  .chatgpt-form-body {
    max-width: 860px;
    margin: -60px auto 0;
    padding: 0 24px 32px;
  }
  .chatgpt-form-intro,
  .chatgpt-form-section {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.08);
  }
  .chatgpt-form-section-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 18px;
    color: #102a43;
  }
  .chatgpt-form-label {
    font-weight: 600;
    display: block;
    margin-bottom: 10px;
  }
  .chatgpt-form-card {
    border: 1px solid #d5e3f0;
    border-radius: 8px;
    overflow: hidden;
  }
  .chatgpt-form-card-title {
    background: #f4f7fb;
    border-bottom: 1px solid #d5e3f0;
    padding: 12px 14px;
    font-weight: 600;
  }
  .ms-forms-input {
    border: none;
    border-radius: 0;
    min-height: 120px;
    padding: 14px;
    background: #fff;
    box-shadow: none !important;
  }
  .ms-forms-table td {
    vertical-align: middle;
    background: #fff;
  }
  .required-field::after {
    content: ' *';
    color: #dc2626;
  }
</style>
<script>
  $(document).ready(function () {
    const $form = $('#onlinemarketing-form');
    const $modal = $('#chatgptProjectModal');
    const modalFields = [
      'chatgpt_project_name',
      'chatgpt_introduction_reason',
      'chatgpt_goal',
      'chatgpt_process_steps',
      'chatgpt_has_existing_process',
      'chatgpt_has_output_examples',
      'chatgpt_has_knowledge_base'
    ];

    $('#open-chatgpt-project-modal').on('click', function () {
      $('#is_chatgpt_project').val('1');
      $modal.modal('show');
    });

    $('#cancel-chatgpt-project').on('click', function () {
      $modal.modal('hide');
    });

    $('#save-chatgpt-project').on('click', function () {
      const missing = modalFields.filter(function (field) {
        const $fields = $form.find('[name="' + field + '"]');
        if ($fields.attr('type') === 'radio') {
          return !$form.find('[name="' + field + '"]:checked').length;
        }
        return !$fields.val().trim();
      });

      if (missing.length) {
        alert('Bitte füllen Sie alle Pflichtfelder des ChatGPT-Projektformulars aus.');
        return;
      }

      $('#chatgpt-project-status').removeClass('d-none');
      $('#is_chatgpt_project').val('1');
      $modal.modal('hide');
    });

    $form.on('submit', function (e) {
      const $submitButton = $form.find('button[type="submit"], input[type="submit"]');

      if (!$('input[name="onlinemarketing_item"]:checked').val()) {
        e.preventDefault();
        alert("Bitte wählen Sie eine Option unter 'Was brauchen Sie ?' aus.");
        $submitButton.prop('disabled', false).html('Einreichen');
        return false;
      }

      if ($('#is_chatgpt_project').val() === '1') {
        const missing = modalFields.filter(function (field) {
          const $fields = $form.find('[name="' + field + '"]');
          if ($fields.attr('type') === 'radio') {
            return !$form.find('[name="' + field + '"]:checked').length;
          }
          return !$fields.val().trim();
        });

        if (missing.length) {
          e.preventDefault();
          alert('Bitte vervollständigen Sie zuerst das Formular „ChatGPT-Projektvorschläge“.');
          $modal.modal('show');
          return false;
        }
      }
    });

    $('#submitter_standort_exception').on('change', function () {
      var newCity = $(this).val();
      var url = "{{ route('room_list') }}/" + newCity;

      $.ajax({ type: "get", url: url }).done(function (data) {
        $("#printer_place").empty();
        $("#printer_room").empty();
        $("body #printer_place").append('<optgroup label="' + data.place.pnname + '" id="' + data.place.id + '" ></optgroup>');
        data['locations'].map((item) => {
          $(`#printer_place #${data.place.id}`).append(new Option(item.address, item.id));
          selectAddresslisten.push(item);
        });
      });
    });

    $('.summernote').summernote({ height: 150, lang: 'de-DE' });

    let selectedFiles = [];
    $("#attachments").on("change", function (e) {
      let files = e.target.files;
      for (let i = 0; i < files.length; i++) selectedFiles.push(files[i]);
      updateFilePreview();
    });

    function updateFilePreview() {
      $("#file-preview").empty();
      selectedFiles.forEach((file, index) => {
        let reader = new FileReader();
        reader.onload = function (e) {
          const isImage = file.type.includes("image");
          const icon = file.type === 'application/pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-muted';
          const preview = isImage
            ? `<img src="${e.target.result}" class="img-thumbnail" width="100">`
            : `<i class="fas ${icon} fa-3x"></i><p class="small mb-1 mt-2">${file.name}</p>`;
          $("#file-preview").append(
            `<div class="p-2 text-center position-relative mr-2 mb-2 border rounded bg-white">${preview}<button type="button" class="btn btn-sm btn-danger remove-file" data-index="${index}">X</button></div>`
          );
        };
        reader.readAsDataURL(file);
      });
    }

    $(document).on("click", ".remove-file", function () {
      let index = $(this).data("index");
      selectedFiles.splice(index, 1);
      updateFilePreview();
    });

    $form.on("submit", function () {
      let fileInput = $("#attachments")[0];
      let dataTransfer = new DataTransfer();
      selectedFiles.forEach(file => dataTransfer.items.add(file));
      fileInput.files = dataTransfer.files;
    });
  });
</script>
@endsection
