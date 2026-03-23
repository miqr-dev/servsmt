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
                              <div class="custom-control custom-radio mb-4 chatgpt-project-link-wrap">
                                <input type="radio" id="itemChatgptProject" name="onlinemarketing_item"
                                  value="chatgpt_project" class="custom-control-input">
                                <label class="custom-control-label" for="itemChatgptProject">
                                  ChatGPT-Projektvorschläge
                                </label>
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

                          <div class="chatgpt-steps-nav mb-4">
                            <button type="button" class="chatgpt-step-pill is-active" data-step-target="1">1. Use Case</button>
                            <button type="button" class="chatgpt-step-pill" data-step-target="2">2. Ziele der KI-Einführung</button>
                            <button type="button" class="chatgpt-step-pill" data-step-target="3">3. Beschreibung des Prozesses</button>
                            <button type="button" class="chatgpt-step-pill" data-step-target="4">4. Sonstige Anforderungen</button>
                          </div>

                          <div class="chatgpt-form-section chatgpt-step-panel is-active" data-step="1">
                            <div class="chatgpt-form-section-title">1. Use Case</div>
                            <label class="chatgpt-form-label required-field">Definiere deinen Anwendungsfall in 3-4 Wörtern („Projektname“)</label>
                            <textarea class="form-control ms-forms-input" name="chatgpt_project_name" rows="3"></textarea>
                            <div class="chatgpt-step-actions text-right mt-4">
                              <button type="button" class="btn chatgpt-next-btn" data-next-step="2">Weiter</button>
                            </div>
                          </div>

                          <div class="chatgpt-form-section chatgpt-step-panel" data-step="2">
                            <div class="chatgpt-form-section-title">2. Ziele der KI-Einführung</div>
                            <label class="chatgpt-form-label required-field">Gründe für die Einführung des KI-Assistenten</label>
                            <div class="chatgpt-form-card">
                              <div class="chatgpt-form-card-title">Engpässe und “Breaking Points” im bestehenden Prozess (Anlass der Einführung)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_introduction_reason" rows="5"></textarea>
                              <div class="chatgpt-upload-block">
                                <label class="chatgpt-upload-label">Anhänge nur für dieses Feld</label>
                                <input type="file" name="chatgpt_introduction_attachments[]" class="form-control-file" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                              </div>
                            </div>
                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Ziele die mit der Einführung des KI-Assistenten verfolgt werden</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_goal" rows="5"></textarea>
                            </div>
                            <div class="chatgpt-form-card mt-4">
                              <div class="chatgpt-form-card-title">Kurzbeschreibung der einzelnen Schritte (Deine “Vorstellung”)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_process_steps" rows="5"></textarea>
                              <div class="chatgpt-upload-block">
                                <label class="chatgpt-upload-label">Anhänge nur für dieses Feld</label>
                                <input type="file" name="chatgpt_process_attachments[]" class="form-control-file" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                              </div>
                            </div>
                            <div class="chatgpt-step-actions d-flex justify-content-between mt-4">
                              <button type="button" class="btn btn-outline-secondary chatgpt-prev-btn" data-prev-step="1">Zurück</button>
                              <button type="button" class="btn chatgpt-next-btn" data-next-step="3">Weiter</button>
                            </div>
                          </div>

                          <div class="chatgpt-form-section chatgpt-step-panel" data-step="3">
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

                            <div class="chatgpt-form-card mt-4 conditional-field d-none" data-visible-when="chatgpt_has_output_examples">
                              <div class="chatgpt-form-card-title">Beispiele für den perfekten Output (falls vorhanden)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_output_examples" rows="6"></textarea>
                              <div class="chatgpt-upload-block">
                                <label class="chatgpt-upload-label">Anhänge nur für dieses Feld</label>
                                <input type="file" name="chatgpt_output_attachments[]" class="form-control-file" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                              </div>
                            </div>

                            <div class="chatgpt-form-card mt-4 conditional-field d-none" data-visible-when="chatgpt_has_knowledge_base">
                              <div class="chatgpt-form-card-title">Vorhandenes Wissen / Knowledge Bases (falls vorhanden)</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_knowledge_base" rows="6"></textarea>
                              <div class="chatgpt-upload-block">
                                <label class="chatgpt-upload-label">Anhänge nur für dieses Feld</label>
                                <input type="file" name="chatgpt_knowledge_attachments[]" class="form-control-file" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                              </div>
                            </div>
                            <div class="chatgpt-step-actions d-flex justify-content-between mt-4">
                              <button type="button" class="btn btn-outline-secondary chatgpt-prev-btn" data-prev-step="2">Zurück</button>
                              <button type="button" class="btn chatgpt-next-btn" data-next-step="4">Weiter</button>
                            </div>
                          </div>

                          <div class="chatgpt-form-section chatgpt-step-panel" data-step="4">
                            <div class="chatgpt-form-section-title">4. Sonstige Anforderungen</div>
                            <label class="chatgpt-form-label">Ergänzende Anforderungen an den KI-Assistenten</label>
                            <div class="chatgpt-form-card">
                              <div class="chatgpt-form-card-title">Sonstige Anforderungen</div>
                              <textarea class="form-control ms-forms-input" name="chatgpt_additional_requirements" rows="8"></textarea>
                              <div class="chatgpt-upload-block">
                                <label class="chatgpt-upload-label">Anhänge nur für dieses Feld</label>
                                <input type="file" name="chatgpt_additional_attachments[]" class="form-control-file" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                              </div>
                            </div>
                            <div class="chatgpt-step-actions d-flex justify-content-between mt-4">
                              <button type="button" class="btn btn-outline-secondary chatgpt-prev-btn" data-prev-step="3">Zurück</button>
                              <button type="button" class="btn chatgpt-save-btn" id="save-chatgpt-project">Formular übernehmen</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer justify-content-start">
                      <button type="button" class="btn btn-outline-secondary" id="cancel-chatgpt-project">Abbrechen</button>
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
    background: linear-gradient(135deg, #661421, #7f1d2d);
    border-bottom: none;
  }
  .chatgpt-form-shell {
    background: #f8f1f2;
    min-height: 100%;
  }
  .chatgpt-form-banner {
    height: 120px;
    background: linear-gradient(135deg, #661421, #8b1e33);
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
    box-shadow: 0 10px 30px rgba(102, 20, 33, 0.08);
  }
  .chatgpt-form-section-title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 18px;
    color: #661421;
  }
  .chatgpt-form-label {
    font-weight: 600;
    display: block;
    margin-bottom: 10px;
  }
  .chatgpt-form-card {
    border: 1px solid #ead4d8;
    border-radius: 8px;
    overflow: hidden;
  }
  .chatgpt-form-card-title {
    background: #f8f1f2;
    border-bottom: 1px solid #ead4d8;
    padding: 12px 14px;
    font-weight: 600;
  }
  .chatgpt-steps-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .chatgpt-step-pill {
    border: 1px solid #d7b0b6;
    background: #fff;
    color: #661421;
    border-radius: 999px;
    padding: 8px 14px;
    font-weight: 600;
  }
  .chatgpt-step-pill.is-active {
    background: #661421;
    border-color: #661421;
    color: #fff;
  }
  .chatgpt-step-panel {
    display: none;
  }
  .chatgpt-step-panel.is-active {
    display: block;
  }
  .chatgpt-next-btn,
  .chatgpt-save-btn {
    background: #661421;
    border-color: #661421;
    color: #fff;
  }
  .chatgpt-next-btn:hover,
  .chatgpt-save-btn:hover {
    background: #7f1d2d;
    border-color: #7f1d2d;
    color: #fff;
  }
  .ms-forms-input {
    border: none;
    border-radius: 0;
    min-height: 120px;
    padding: 14px;
    background: #fff;
    box-shadow: none !important;
  }
  .chatgpt-upload-block {
    border-top: 1px solid #ead4d8;
    padding: 12px 14px 14px;
    background: #fcf7f8;
  }
  .chatgpt-upload-label {
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
    color: #661421;
    margin-bottom: 8px;
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

    const stepRequirements = {
      1: ['chatgpt_project_name'],
      2: ['chatgpt_introduction_reason', 'chatgpt_goal', 'chatgpt_process_steps'],
      3: ['chatgpt_has_existing_process', 'chatgpt_has_output_examples', 'chatgpt_has_knowledge_base'],
      4: []
    };

    function validateFields(fields) {
      return fields.filter(function (field) {
        const $fields = $form.find('[name="' + field + '"]');
        if ($fields.attr('type') === 'radio') {
          return !$form.find('[name="' + field + '"]:checked').length;
        }
        return !$fields.val().trim();
      });
    }

    function goToStep(step) {
      $('.chatgpt-step-panel').removeClass('is-active');
      $('.chatgpt-step-panel[data-step="' + step + '"]').addClass('is-active');
      $('.chatgpt-step-pill').removeClass('is-active');
      $('.chatgpt-step-pill[data-step-target="' + step + '"]').addClass('is-active');
      updateConditionalFields();
    }

    function updateConditionalFields() {
      $('[data-visible-when="chatgpt_has_output_examples"]').toggleClass('d-none', $form.find('[name="chatgpt_has_output_examples"]:checked').val() !== '1');
      $('[data-visible-when="chatgpt_has_knowledge_base"]').toggleClass('d-none', $form.find('[name="chatgpt_has_knowledge_base"]:checked').val() !== '1');
    }

    $('#cancel-chatgpt-project').on('click', function () {
      $modal.modal('hide');
    });

    $('input[name="onlinemarketing_item"]').on('change', function () {
      if ($(this).val() === 'chatgpt_project') {
        $('#is_chatgpt_project').val('1');
        goToStep(1);
        $modal.modal('show');
      } else {
        $('#is_chatgpt_project').val('0');
        $('#chatgpt-project-status').addClass('d-none');
      }
    });

    $('input[name="chatgpt_has_output_examples"], input[name="chatgpt_has_knowledge_base"]').on('change', function () {
      updateConditionalFields();
    });

    $('.chatgpt-next-btn').on('click', function () {
      const currentStep = Number($(this).closest('.chatgpt-step-panel').data('step'));
      const nextStep = Number($(this).data('next-step'));
      const missing = validateFields(stepRequirements[currentStep] || []);

      if (missing.length) {
        alert('Bitte füllen Sie zuerst alle Pflichtfelder dieses Schritts aus.');
        return;
      }

      goToStep(nextStep);
    });

    $('.chatgpt-prev-btn').on('click', function () {
      goToStep(Number($(this).data('prev-step')));
    });

    $('.chatgpt-step-pill').on('click', function () {
      const targetStep = Number($(this).data('step-target'));
      const activeStep = Number($('.chatgpt-step-panel.is-active').data('step'));

      if (targetStep > activeStep) {
        const missing = validateFields(stepRequirements[activeStep] || []);
        if (missing.length) {
          alert('Bitte füllen Sie zuerst alle Pflichtfelder dieses Schritts aus.');
          return;
        }
      }

      goToStep(targetStep);
    });

    $('#save-chatgpt-project').on('click', function () {
      const missing = validateFields(modalFields);

      if (missing.length) {
        const firstMissing = missing[0];
        if ((stepRequirements[1] || []).includes(firstMissing)) goToStep(1);
        else if ((stepRequirements[2] || []).includes(firstMissing)) goToStep(2);
        else if ((stepRequirements[3] || []).includes(firstMissing)) goToStep(3);

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

      if ($('input[name="onlinemarketing_item"]:checked').val() === 'chatgpt_project' && $('#is_chatgpt_project').val() === '1') {
        const missing = validateFields(modalFields);

        if (missing.length) {
          e.preventDefault();
          alert('Bitte vervollständigen Sie zuerst das Formular „ChatGPT-Projektvorschläge“.');
          const firstMissing = missing[0];
          if ((stepRequirements[1] || []).includes(firstMissing)) goToStep(1);
          else if ((stepRequirements[2] || []).includes(firstMissing)) goToStep(2);
          else if ((stepRequirements[3] || []).includes(firstMissing)) goToStep(3);
          else goToStep(4);
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
    updateConditionalFields();
  });
</script>
@endsection
