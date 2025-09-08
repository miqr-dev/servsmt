<div class="modal fade" id="listen" tabindex="-1" data-keyboard="false" data-backdrop="static" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 1080px!important;"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Listen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
            </div>
                <div class="modal-body">
                <style>
                    @media print {
                        body {
                        text-align: center;
                        }
                        table {
                            margin-right: auto;
                            margin-left: auto;
                            text-align: center;
                            border-collapse: collapse;
                            width: 90%;
                        }

                        td, th {
                            border: 1px solid black;
                            text-align: left;
                            padding: 8px;
                        }
                        tr:nth-child(even) {
                            background-color: #dddddd;
                        }
                        .print_div {
                                font-size: 16pt;
                                margin-bottom: 20px;
                                visibility: visible !important;
                        }
                        .non_print {
                                visibility: hidden;
                        }
                    }
                </style>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-4 non_print">
                            <select id="location_id_listen" name="location_id" class="form-control" required>
                            </select>
                        </div>
                        <div class="form-group col-md-4 non_print">
                            <select id="room_id_listen" name="room_id" class="form-control" required>
                            </select>
                        </div>
                    </div>
                    <div class="print_div" style="visibility: hidden;">
                    </div>
                        <table class="table" id="table_listen" style="display: none;">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Inventarnummer</th>
                                    <th scope="col">Gerätename</th>
                                    <th scope="col">Geräteart</th>
                                    <th scope="col">Gerätetyp</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                </div>
                <div class="modal-footer">
                  <button button="button" id="print_list" class="btn btn-success">Drucken</button>
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Verwerfen</button>
                </div>
        </div>
    </div>
</div>

