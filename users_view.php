<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">

        </div><!-- /.col -->
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="<?= site_url($this->session->userdata('userMenu') . '/home'); ?>">Home</a></li>
            <li class="breadcrumb-item"><i>Pengguna</i></li>
          </ol>
        </div><!-- /.col -->
      </div><!-- /.row -->
    </div><!-- /.container-fluid -->
  </div>
  <!-- /.content-header -->
  <!-- Main content -->
  <div class="content">
    <div class="container-fluid">
      <div class="mb-4">
        <h3 class="m-0 text-dark">Data Pengguna</h3>
      </div>
      <div class="mb-3">
        <button class="btn btn-success" onclick="add_users()" data-toggle="modal" data-target="#usersModal"><i class="fas fa-plus-circle"></i> Tambah Pengguna</button>
        <button class="btn btn-default" onclick="reload_table()"><i class="fas fa-sync-alt"></i> Reload</button>
        <br />
        <br />

        <div class="table-responsive">
          <table id="table" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
            <thead>
              <tr style="color:#364999; background: #efddcc;">
                <th style="vertical-align:middle">#</th>
                <th style="vertical-align:middle"></th>
                <th style="vertical-align:middle">Role</th>
                <th style="vertical-align:middle">Nama</th>
                <th style="vertical-align:middle">Kontak</th>
                <th style="width:125px;"></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
</div>
<?php
$this->load->view('layout/foot');
?>
<!-- /.container-fluid -->

<script type="text/javascript">
  var save_method; //for save method string
  var table;

  $(document).ready(function() {

    //datatables
    table = $('#table').DataTable({

      "processing": true, //Feature control the processing indicator.
      "serverSide": true, //Feature control DataTables' server-side processing mode.
      "order": [], //Initial no order.
      "language": {
        "url": "<?php echo base_url('assets/vendor/datatables/bahasa.json') ?>"
      },

      // Load data for the table's content from an Ajax source
      "ajax": {
        "url": "<?php echo site_url('owner/users/ajax_list') ?>",
        "type": "POST"
      },
      //Set column definition initialisation properties.
      "columnDefs": [{
          "width": "15%",
          "targets": -1
        },
        {
          "targets": [-1, 0, 1], //last column
          "orderable": false, //set not orderable
        }, {
          "className": 'control',
          "orderable": false,
          "targets": 0
        }
      ],

    });

    $("input").change(function() {
      $(this).parent().parent().removeClass('text-danger');
      $(this).next().empty();
    });
    $("textarea").change(function() {
      $(this).parent().parent().removeClass('text-danger');
      $(this).next().empty();
    });
    $("select").change(function() {
      $(this).parent().parent().removeClass('text-danger');
      $(this).next().empty();
    });

  });

  function add_users() {
    save_method = 'add';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('text-danger'); // clear error class
    $('.help-block').empty(); // clear error string
    $('#usersModal').modal('show'); // show bootstrap modal
  }

  function edit_users(id) {
    save_method = 'update';
    $('#form')[0].reset(); // reset form on modals
    $('.form-group').removeClass('text-danger'); // clear error class
    $('.help-block').empty(); // clear error string

    //Ajax Load data from ajax
    $.ajax({
      url: "<?php echo site_url('owner/users/ajax_edit/') ?>/" + id,
      type: "GET",
      dataType: "JSON",
      success: function(data) {
        $('[name="user_id"]').val(data.user_id);
        $('[name="nama"]').val(data.nama);
        $('[name="nik"]').val(data.nik);
        $('[name="kelamin"]').val(data.kelamin);
        $('[name="pob"]').val(data.pob);
        $('[name="dob"]').val(data.dob);
        $('[name="alamat"]').val(data.alamat);
        $('[name="mail"]').val(data.mail);
        $('[name="telepon"]').val(data.telepon);
        $('[name="hp"]').val(data.hp);
        $('[name="username"]').val(data.username);
        $('[name="role_id"]').val(data.role_id);
        $('#usersModal').modal('show'); // show bootstrap modal when complete loaded
      },
      error: function(jqXHR, textStatus, errorThrown) {
        alert('Error mendapatkan data');
      }
    });
  }

  function reload_table() {
    table.ajax.reload(null, false); //reload datatable ajax 
  }

  function save() {
    $('#btnSave').text('saving...'); //change button text
    $('#btnSave').attr('disabled', true); //set button disable 
    var url;

    if (save_method == 'add') {
      url = "<?php echo site_url('owner/users/ajax_add') ?>";
    } else {
      url = "<?php echo site_url('owner/users/ajax_update') ?>";
    }

    var formData = new FormData($('#form')[0]);

    // ajax adding data to database
    $.ajax({
      url: url,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "JSON",
      success: function(data) {

        if (data.status) //if success close modal and reload ajax table
        {
          $('#usersModal').modal('hide');
          reload_table();
        } else {
          for (var i = 0; i < data.inputerror.length; i++) {
            $('[name="' + data.inputerror[i] + '"]').parent().parent().addClass('text-danger'); //select parent twice to select div form-group class and add text-danger class
            $('[name="' + data.inputerror[i] + '"]').next().text(data.error_string[i]); //select span help-block class set text error string
          }
        }

        $('#btnSave').text('save'); //change button text
        $('#btnSave').attr('disabled', false); //set button enable 


      },
      error: function(jqXHR, textStatus, errorThrown) {
        alert('Error adding / update data');
        $('#btnSave').text('save'); //change button text
        $('#btnSave').attr('disabled', false); //set button enable 

      }
    });
  }

  function delete_users(id) {
    if (confirm('Apakah anda yakin untuk menghapus data?')) {
      // ajax delete data to database
      $.ajax({
        url: "<?php echo site_url('owner/users/ajax_delete') ?>/" + id,
        type: "POST",
        dataType: "JSON",
        success: function(data) {
          //if success reload ajax table
          $('#usersModal').modal('hide');
          reload_table();
        },
        error: function(jqXHR, textStatus, errorThrown) {
          alert('Error deleting data');
        }
      });

    }
  }
</script>

<div class="modal fade" id="usersModal" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Form Pengguna</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body form">
        <form action="#" id="form" class="form-horizontal">
          <input type="hidden" value="" name="user_id" />
          <div class="form-body">
            <div class="form-group row">
              <label class="control-label col-md-4">Role</label>
              <div class="col-md-3">
                <select name="role_id" class="form-control">
                  <?php
                  $getRole = $this->db->get('role');
                  foreach ($getRole->result() as $role) {
                  ?>
                    <option value="<?= $role->role_id; ?>"><?= $role->role; ?></option>
                  <?php
                  }
                  ?>
                </select>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">NIK</label>
              <div class="col-md-3">
                <input type="text" name="nik" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Nama</label>
              <div class="col-md">
                <input type="text" name="nama" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Jenis Kelamin</label>
              <div class="col-md-3">
                <select name="kelamin" class="form-control" required>
                  <option value="0">Perempuan</option>
                  <option value="1">Laki-laki</option>
                </select>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Tempat Tgl. Lahir</label>
              <div class="col-md-3">
                <input type="text" name="pob" class="form-control" required>
                <span class="help-block"></span>
              </div>
              <div class="col-md-3">
                <input type="date" name="dob" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Alamat</label>
              <div class="col-md">
                <textarea name="alamat" class="form-control" required rows="5"></textarea>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Email</label>
              <div class="col-md">
                <input type="email" name="mail" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Telepon</label>
              <div class="col-md-3">
                <input type="text" name="telepon" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">HP/WA</label>
              <div class="col-md-3">
                <input type="text" name="hp" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Foto</label>
              <div class="col-md">
                <input type="file" name="foto" class="form-control" accept="image/*">
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Username</label>
              <div class="col-md">
                <input type="text" name="username" class="form-control" required>
                <span class="help-block"></span>
              </div>
            </div>
            <div class="form-group row">
              <label class="control-label col-md-4">Password</label>
              <div class="col-md">
                <input type="password" name="psword" class="form-control">
                <span class="help-block"></span>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnSave" onclick="save()" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</body>

</html>