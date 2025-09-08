@extends('layouts.admin_layout.admin_layout')

@section('content')
  <!-- Main content -->
<section class="content">
  <div class="row">
  <!-- first box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Berlin Bemerkungen</label>
        <textarea id="b-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->berlin ? $cityNotes->berlin : ''  }}</textarea>
      </div>
    </div>
  <!-- second box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Berlin II Bemerkungen</label>
        <textarea id="b2-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->berlin2 ? $cityNotes->berlin2 : ''  }}</textarea>
      </div>
    </div>
  <!-- third box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Chemnitz Bemerkungen</label>
        <textarea id="c-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->chemnitz ? $cityNotes->chemnitz : ''  }}</textarea>
      </div>
    </div>
  <!-- forth box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Dresden Bemerkungen</label>
        <textarea id="d-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->dresden ? $cityNotes->dresden : ''  }}</textarea>
      </div>
    </div>
  <!-- fifth box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Leipzig Bemerkungen</label>
        <textarea id="l-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->leipzig ? $cityNotes->leipzig : ''  }}</textarea>
      </div>
    </div>
  <!-- sixth box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Suhl Bemerkungen</label>
        <textarea id="s-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->suhl ? $cityNotes->suhl : ''  }}</textarea>
      </div>
    </div>
  <!-- seventh box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Erfurt Bemerkungen</label>
        <textarea id="e-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->erfurt ? $cityNotes->erfurt : ''  }}</textarea>
      </div>
    </div>
  <!-- Aids box -->
    <div class="mailbox-controls col-md-4">
      <div class="form-group">
        <label style="color:#661421;">Döbeln Bemerkungen</label>
        <textarea id="dbn-bemerkungen" class="form-control bemerkungen" rows="8" style="resize:none;" disabled>{{ @$cityNotes->döbeln  ? $cityNotes->döbeln  : ''  }}</textarea>
      </div>
    </div>
</div>
  <button class="form-control col-md-2 float-right mt-2" id="bemerkungen-button"><i class="fa-solid fa-pen-to-square"></i></button>

</section>
@endsection
@section('script')



@endsection

