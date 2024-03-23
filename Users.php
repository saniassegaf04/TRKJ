<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
//class user merupakan turunan dari ci-controller
{

  public function __construct()
  {
    parent::__construct();
   
    
    $this->load->model('users_model', 'users');
    //this load adalh untuk menjalan kan perintah tertentu, (membuka model) dan users di samping nya dalah inisial untuk memangil user--model
  }

  public function index()
  {
    //$this->load->helper('url');
    $header['judul'] = "Pengguna";
    $this->load->view('layout/head', $header);
    $this->load->view('users_view');
  }

  public function ajax_list()
  {
    $pencarian = array('user_id', 'foto', 'role', 'nama', 'alamat');

    $list = $this->users->get_datatables('user_role_vu', $pencarian);
    $data = array();
    $no = $_POST['start'];
    foreach ($list as $users) {
      $no++;
      $row = array();
      $row[] = $no;
      $row[] = '<img src="' . base_url('images/users/') . $users->foto . '" class="rounded-circle" height="50px">';
      $row[] = $users->role;
      $row[] = $users->nama;
      $row[] = "$users->alamat<br>$users->telepon / $users->hp";

      //add html for action
      $row[] = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Aksi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="#" title="Edit" onclick="edit_users(' . "'" . $users->user_id . "'" . ')"><i class="fas fa-edit"></i> Edit</a>
                            <a class="dropdown-item" href="#" title="Hapus" onclick="delete_users(' . "'" . $users->user_id . "'" . ')"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </div>';

      $data[] = $row;
    }

    $output = array(
      "draw" => $_POST['draw'],
      "recordsTotal" => $this->users->count_all('user_role_vu', $pencarian),
      "recordsFiltered" => $this->users->count_filtered('user_role_vu', $pencarian),
      "data" => $data,
    );
    //output to json format
    echo json_encode($output);
  }

  public function ajax_edit($idusers_siswa)
  {
    $data = $this->users->get_by_id($idusers_siswa, 'users', 'user_id');
    echo json_encode($data);
  }

  public function ajax_add()
  {
    $this->_validate();
    $passwordEnc = password_hash($this->input->post('psword'), PASSWORD_DEFAULT);
    $data = array(
      'nik' => $this->input->post('nik'),
      'nama' => $this->input->post('nama'),
      'kelamin' => $this->input->post('kelamin'),
      'pob' => $this->input->post('pob'),
      'dob' => $this->input->post('dob'),
      'alamat' => $this->input->post('alamat'),
      'mail' => $this->input->post('mail'),
      'telepon' => $this->input->post('telepon'),
      'hp' => $this->input->post('hp'),
      'username' => $this->input->post('username'),
      'psword' => $passwordEnc,
      'role_id' => $this->input->post('role_id')
    );

    if (!empty($_FILES['foto']['name'])) {
      $upload = $this->_do_upload();
      $data['foto'] = $upload;
    }

    $insert = $this->users->save($data, 'users');
    echo json_encode(array("status" => TRUE));
  }

  public function ajax_update()
  {
    $passwordEnc = password_hash($this->input->post('psword'), PASSWORD_DEFAULT);
    $data = array(
      'nik' => $this->input->post('nik'),
      'nama' => $this->input->post('nama'),
      'kelamin' => $this->input->post('kelamin'),
      'pob' => $this->input->post('pob'),
      'dob' => $this->input->post('dob'),
      'alamat' => $this->input->post('alamat'),
      'mail' => $this->input->post('mail'),
      'telepon' => $this->input->post('telepon'),
      'hp' => $this->input->post('hp'),
      'username' => $this->input->post('username'),
      'role_id' => $this->input->post('role_id')
    );

    if ($this->input->post('psword') != '') {
      $data['psword'] = $passwordEnc;
    }

    if (!empty($_FILES['foto']['name'])) {
      $person = $this->users->get_by_id($this->input->post('user_id'), 'users', 'user_id');
      if (file_exists('images/users/' . $person->foto) && $person->foto)
        unlink('images/users/' . $person->foto);

      $upload = $this->_do_upload();
      $data['foto'] = $upload;
    }
    $this->users->update('users', array('user_id' => $this->input->post('user_id')), $data);
    echo json_encode(array("status" => TRUE));
  }

  public function ajax_delete($idusers_siswa)
  {
    $data = array(
      'deleted_at' => date("Y-m-d H:i:s")
    );
    $this->users->update('users', array('user_id' => $idusers_siswa), $data);
    echo json_encode(array("status" => TRUE));
  }

  private function _do_upload()
  {
    $config['upload_path'] = "images/users/"; //path folder
    $config['allowed_types'] = 'jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
    $config['file_name'] = $this->input->post('nik');

    $this->load->library('upload');

    $this->upload->initialize($config);
    if ($this->upload->do_upload('foto')) {
      $gbr = $this->upload->data();
      //Compress Image
      $config['image_library'] = 'gd2';
      $config['source_image'] = "images/users/" . $gbr['file_name'];
      $config['create_thumb'] = FALSE;
      $config['maintain_ratio'] = TRUE;
      $config['quality'] = '25%';
      $config['width'] = 2000;
      $config['height'] = 3000;
      $config['new_image'] = "images/users/" . $gbr['file_name'];
      $this->load->library('image_lib', $config);
      $this->image_lib->resize();

      $gambar = $gbr['file_name'];
      /* $judul = $this->input->post('xjudul');
        $this->m_upload->simpan_upload($judul, $gambar); */
      return $gbr['file_name'];
    } else {
      $data['inputerror'][] = 'foto';
      $data['error_string'][] = 'Upload error: ' . $this->upload->display_errors('', ''); //show ajax error
      $data['status'] = FALSE;
      echo json_encode($data);
      exit();
    }
  }

  private function _validate()
  {
    $data = array();
    $data['error_string'] = array();
    $data['inputerror'] = array();
    $data['status'] = TRUE;

    $this->db->where('nik', $this->input->post('nik'));
    $cekusers = $this->db->get('users');

    if ($cekusers->num_rows() > 0) {
      $data['inputerror'][] = 'nik';
      $data['error_string'][] = 'NIK Sudah terdaftar';
      $data['status'] = FALSE;
    }

    if ($data['status'] === FALSE) {
      echo json_encode($data);
      exit();
    }
  }
}
