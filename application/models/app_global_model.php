<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class app_global_model extends CI_Model {

	/**
	 * @author : Gede Lumbung
	 * @web : http://gedelumbung.com
	 **/
	public function generate_captcha()
	{
		$vals = array(
			'img_path' => './captcha/',
			'img_url' => base_url().'captcha/',
			'font_path' => './system/fonts/impact.ttf',
			'img_width' => '150',
			'img_height' => 40
			);
		$cap = create_captcha($vals);
		$datamasuk = array(
			'captcha_time' => $cap['time'],
			//'ip_address' => $this->input->ip_address(),
			'word' => $cap['word']
			);
		$expiration = time()-3600;
		$this->db->query("DELETE FROM captcha WHERE captcha_time < ".$expiration);
		$query = $this->db->insert_string('captcha', $datamasuk);
		$this->db->query($query);
		return $cap['image'];
	}
	 
	public function generate_menu($parent=0,$posisi,$hasil,$cls_css=NULL)
	{
		$where['id_parent']=$parent;
		$where['posisi']=$posisi;
		$w = $this->db->get_where("dlmbg_menu",$where);
		$w_q = $this->db->get_where("dlmbg_menu",$where)->row();
		if(($w->num_rows())>0)
		{
			$hasil .= "<ul id='".$cls_css."'>";
		}
		foreach($w->result() as $h)
		{
			$where_sub['id_parent']=$h->id_menu;
			$w_sub = $this->db->get_where("dlmbg_menu",$where_sub);
			if(($w_sub->num_rows())>0)
			{
				$hasil .= "<li><a href='".base_url()."web/web/pages/".$h->id_menu."/".url_title(strtolower($h->menu))."'>".$h->menu." &raquo;</a>";
			}
			else
			{
				if($h->id_parent==0)
				{
					$hasil .= "<li><a href='".base_url()."web/web/pages/".$h->id_menu."/".url_title(strtolower($h->menu))."'>".$h->menu."</a>";
				}
				else
				{
					$hasil .= "<li><a href='".base_url()."web/web/pages/".$h->id_menu."/".url_title(strtolower($h->menu))."'>&raquo; ".$h->menu."</a>";
				}
			}
			$hasil = $this->generate_menu($h->id_menu,$posisi,$hasil);
			$hasil .= "</li>";
		}
		if(($w->num_rows)>0)
		{
			$hasil .= "</ul>";
		}
		return $hasil;
	}
	 
	public function generate_menu_bidang()
	{
		$hasil="";
		$w = $this->db->get("dlmbg_super_bidang");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><a href='".base_url()."web/bidang/profil/".$h->id_super_bidang."/".url_title(strtolower($h->bidang))."' title='Bidang ".$h->bidang."'>Bidang ".$h->bidang."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_link_terkait()
	{
		$hasil="";
		$w = $this->db->get("dlmbg_super_link_terkait");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><a href='".$h->url."' title='".$h->nama_link."' target='_blank'>".$h->nama_link."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_pengumuman($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by('id_multi_pengumuman','desc')->get_where("dlmbg_multi_pengumuman",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/pengumuman/detail/".$h->id_multi_pengumuman."/".$h->tipe_user."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_agenda($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by('id_multi_agenda','desc')->get_where("dlmbg_multi_agenda",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/agenda/detail/".$h->id_multi_agenda."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_polling()
	{
		$hasil = "<div style='padding:0px 5px 0px 5px;'>";
		$hasil .= form_open('web/polling/simpan');
		$where['aktif'] = 1;
		$w = $this->db->get_where("dlmbg_super_pertanyaan_poll",$where);
		foreach($w->result() as $h)
		{
			$hasil .= "<input type='hidden' name='id_soal' value='".$h->id_super_pertanyaan_poll."'>";
			$hasil .= "<b>".$h->pertanyaan."</b>";
		}
		$hasil .= "<br>";
		$where_jawaban['id_pertanyaan'] = $h->id_super_pertanyaan_poll;
		$jawaban_polling = $this->db->get_where("dlmbg_super_jawaban_poll",$where_jawaban);
		foreach($jawaban_polling->result() as $jawaban)
		{
			$hasil .= "<span style='padding:5px;'><input type='radio' name='polling' value='".$jawaban->id_super_jawaban_poll."' class='radio-class'> ".$jawaban->jawaban."</span><br>";
		}
		$hasil .= '<br /><span><input type="submit" value="PILIH" /></span>
<a href="'.base_url().'web/polling"><span class="poll">HASIL POLLING</span></a></span><br />';
		$hasil .= "</div>";
		$hasil .= form_close();
		return $hasil;
	}
	 
	public function generate_menu_statistik()
	{
		$hasil = "";
		$hasil .= "<li>Browser : <b>".$this->agent->browser().' '.$this->agent->version()."</b></li>";
		$hasil .= "<li>OS : <b>".$this->agent->platform()."</b></li>";
		$hasil .= "<li>Dikunjungi sebanyak : <b>".$this->db->get("dlmbg_counter")->num_rows()."</b> kali</li>";
		setcookie("pengunjung", "sudah berkunjung", time() + 900 * 24);
		if (!isset($_COOKIE["pengunjung"])) {
			$d_in['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$d_in['tanggal'] = gmdate("d-M-Y H:i:s",time()+3600*9);
			$this->db->insert("dlmbg_counter",$d_in);
		}
		return $hasil;
	}
	 
	public function generate_menu_artikel_sekolah($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by("id_sekolah_artikel","DESC")->get_where("dlmbg_sekolah_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/artikel_sekolah/detail/".$h->id_sekolah_artikel."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_artikel_pengawas($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by("id_pengawas_artikel","DESC")->get_where("dlmbg_pengawas_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/artikel_pengawas/detail/".$h->id_pengawas_artikel."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_berita_pengawas($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by("id_pengawas_berita","DESC")->get_where("dlmbg_pengawas_berita",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/berita_pengawas/detail/".$h->id_pengawas_berita."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_agenda_pengawas($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by("id_pengawas_agenda","DESC")->get_where("dlmbg_pengawas_agenda",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/agenda_pengawas/detail/".$h->id_pengawas_agenda."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_pengumuman_pengawas($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by("id_pengawas_pengumuman","DESC")->get_where("dlmbg_pengawas_pengumuman",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4><a href='".base_url()."web/pengumuman_pengawas/detail/".$h->id_pengawas_pengumuman."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		return $hasil;
	}
	 
	public function generate_menu_galeri_kegiatan($limit,$offset)
	{
		$hasil="";
		$w = $this->db->get("dlmbg_super_galeri_dinas",$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div class="border-photo-gallery"><div class="hide-photo-gallery"><a href="'.base_url().'asset/images/galeri/medium/'.$h->gambar.'" rel="galeri" title="'.$h->judul.'"><img src="'.base_url().'asset/images/galeri/thumb/'.$h->gambar.'" title="'.$h->judul.'" /></a></div></div>';
		}
		return $hasil;
	}
	 
	public function generate_index_album_galeri_kegiatan($limit,$offset)
	{
		$hasil="";

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;
		
		$tot_hal = $this->db->get("dlmbg_super_album_galeri_dinas");
		$config['base_url'] = base_url() . 'web/galeri/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$w = $this->db->get("dlmbg_super_album_galeri_dinas",$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div class="border-photo-album">
			<a href="'.base_url().'web/galeri/album/'.$h->id_abum_galeri_dinas.'" title="'.$h->nama_album.'">
			<img src="'.base_url().'asset/theme/'.$_SESSION['site_theme'].'/images/album-icon.png" width="100" title="'.$h->nama_album.'" />
			<div class="cleaner_h5"></div>
			<h4>'.$h->nama_album.'</h4>
			</a></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_galeri_kegiatan($id_param,$limit,$offset)
	{
		$hasil="";

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;
		
		$where['id_album'] = $id_param;
		$tot_hal = $this->db->get_where("dlmbg_super_galeri_dinas",$where);
		$config['base_url'] = base_url() . 'web/galeri/album/'.$id_param.'/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$w = $this->db->get_where("dlmbg_super_galeri_dinas",$where,$limit,$offset);
		if($w->num_rows()==0)
		{
			$hasil = "<h3>Belum ada foto untuk album ini</h3>";
			return $hasil;
			break;
		}
		
		foreach($w->result() as $h)
		{
			$hasil .= '<div class="border-photo-gallery-index"><div class="hide-photo-gallery-index"><a href="'.base_url().'asset/images/galeri/medium/'.$h->gambar.'" rel="galeri" title="'.$h->judul.'"><img src="'.base_url().'asset/images/galeri/thumb/'.$h->gambar.'" title="'.$h->judul.'" /></a></div></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_menu_slider_content($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['headline'] = 'y';
		$w = $this->db->order_by('id_multi_berita','desc')->get_where("dlmbg_multi_berita",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<li><img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" width="470" height="300" />
				<div class="lof-main-item-desc">
					<h4><a href="'.base_url().'web/berita/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'">'.substr($h->judul,0,45).'...</a></h4>
					<p>'.strip_tags(substr($h->isi,0,150)).'....</p>
				</div>
			</li>';
		}
		return $hasil;
	}
	 
	public function generate_menu_slider_navigator($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['headline'] = 'y';
		$w = $this->db->order_by('id_multi_berita','desc')->get_where("dlmbg_multi_berita",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<li> 
				<div><img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" height="15"/>
					<h3>'.substr($h->judul,0,45).'...</h3> 
					<span>'.strip_tags(substr($h->isi,0,75)).'....</span> 
				</div>
			</li>';
		}
		return $hasil;
	}
	 
	public function generate_daftar_berita($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$w = $this->db->order_by('id_multi_berita','desc')->get_where("dlmbg_multi_berita",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/berita/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.strip_tags(substr($h->isi,0,200)).'.... <a href="'.base_url().'web/berita/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_index_berita($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_bidang']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_bidang'] = $filter['id_bidang']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_bidang='".$where['id_bidang']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->query("select * from dlmbg_multi_berita a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/berita/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_multi_berita a where a.stts='".$where['stts']."' ".$query_add." 
		order by id_multi_berita DESC limit ".$offset.", ".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/berita/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/berita/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_berita_pengawas($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_unit_kerja']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_unit_kerja'] = $filter['id_unit_kerja']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_unit_kerja='".$where['id_unit_kerja']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->query("select * from dlmbg_pengawas_berita a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/berita_pengawas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_pengawas_berita a where a.stts='".$where['stts']."' ".$query_add." 
		order by id_pengawas_berita DESC limit ".$offset.", ".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/berita_pengawas/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/berita_pengawas/detail/'.$h->id_pengawas_berita.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/berita_pengawas/detail/'.$h->id_pengawas_berita.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_detail_berita($id_param,$tipe)
	{
		$hasil="";
		$w="";
		if($tipe=="dinas")
		{
			$w = $this->db->query("select a.id_multi_berita, a.judul, a.tipe_user, a.isi, a.gambar, a.tanggal, b.bidang, c.nama_admin_dinas as usr from dlmbg_multi_berita a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_dinas c on a.id_user=c.id_admin_dinas where a.id_multi_berita='".$id_param."' and a.stts='1'");
		}
		else
		{
			$w = $this->db->query("select a.id_multi_berita, a.judul, a.tipe_user, a.isi, a.gambar, a.tanggal, b.bidang, c.nama_super_admin as usr from dlmbg_multi_berita a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_super c on a.id_user=c.id_admin_super where a.id_multi_berita='".$id_param."' and a.stts='1'");
		}
		foreach($w->result() as $h)
		{
			$hasil .= '
			<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div>
			<div class="cleaner_h10"></div>
			<span style="float:none; width:380px; font-size:12px; font-weight:bold; text-align:right; padding-top:3px;">
			Ditulis oleh : '.$h->usr.' - Bidang : '.$h->bidang.'
			</span>
			<div id="news-list-detail">
			<img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_detail_berita_pengawas($id_param)
	{
		$hasil="";
		$w = $this->db->query("select a.id_pengawas_berita, a.judul, a.isi, a.gambar, a.tanggal, b.unit_kerja, c.nama_user_pengawas as usr from dlmbg_pengawas_berita a left join dlmbg_super_unit_kerja b on a.id_unit_kerja=b.id_super_unit_kerja left join dlmbg_user_pengawas c on a.id_user_pengawas=c.id_user_pengawas where a.id_pengawas_berita='".$id_param."' and a.stts='1'");
		
		foreach($w->result() as $h)
		{
			$hasil .= '
			<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div>
			<div class="cleaner_h10"></div>
			<span style="float:none; width:380px; font-size:12px; font-weight:bold; text-align:right; padding-top:3px;">
			Ditulis oleh : '.$h->usr.' - Unit Kerja : '.$h->unit_kerja.'
			</span>
			<div id="news-list-detail">
			<img src="'.base_url().'asset/images/berita-pengawas/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_index_pengumuman($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_bidang']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_bidang'] = $filter['id_bidang']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_bidang='".$where['id_bidang']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$tot_hal = $this->db->query("select * from dlmbg_multi_pengumuman a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/pengumuman/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_multi_pengumuman a where a.stts='".$where['stts']."' ".$query_add." order 
		by id_multi_pengumuman DESC LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/pengumuman/detail/".$h->id_multi_pengumuman."/".$h->tipe_user."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_pengumuman_pengawas($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_unit_kerja']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_unit_kerja'] = $filter['id_unit_kerja']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_unit_kerja='".$where['id_unit_kerja']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$tot_hal = $this->db->query("select * from dlmbg_pengawas_pengumuman a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/pengumuman_pengawas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_pengawas_pengumuman a where a.stts='".$where['stts']."' ".$query_add." order 
		by id_pengawas_pengumuman DESC LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/pengumuman_pengawas/detail/".$h->id_pengawas_pengumuman."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_detail_pengumuman($id_param,$tipe)
	{
		$hasil="";
		$w="";
		if($tipe=="dinas")
		{
			$w = $this->db->query("select a.id_multi_pengumuman, a.judul, a.tipe_user, a.isi, a.tanggal, b.bidang, c.nama_admin_dinas as usr from dlmbg_multi_pengumuman a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_dinas c on a.id_user=c.id_admin_dinas where a.id_multi_pengumuman='".$id_param."' and a.stts='1'");
		}
		else
		{
			$w = $this->db->query("select a.id_multi_pengumuman, a.judul, a.tipe_user, a.isi, a.tanggal, b.bidang, c.nama_super_admin as usr from dlmbg_multi_pengumuman a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_super c on a.id_user=c.id_admin_super where a.id_multi_pengumuman='".$id_param."' and a.stts='1'");
		}
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div><div style="float:none; width:380px; font-size:12px; font-weight:bold; padding-top:5px;">
			Ditulis oleh : '.$h->usr.' - Bidang : '.$h->bidang.'
			</div>
			<div id="news-list-detail">
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_detail_pengumuman_pengawas($id_param)
	{
		$hasil="";
			
		$w = $this->db->query("select a.id_pengawas_pengumuman, a.judul, a.isi, a.tanggal, b.unit_kerja, c.nama_user_pengawas as usr from dlmbg_pengawas_pengumuman a left join dlmbg_super_unit_kerja b on a.id_unit_kerja=b.id_super_unit_kerja left join dlmbg_user_pengawas c on a.id_user_pengawas=c.id_user_pengawas where a.id_pengawas_pengumuman='".$id_param."' and a.stts='1'");
		
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div><div style="float:none; width:380px; font-size:12px; font-weight:bold; padding-top:5px;">
			Ditulis oleh : '.$h->usr.' - Unit Kerja : '.$h->unit_kerja.'
			</div>
			<div id="news-list-detail">
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_index_agenda($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_bidang']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_bidang'] = $filter['id_bidang']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_bidang='".$where['id_bidang']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$tot_hal = $this->db->query("select * from dlmbg_multi_agenda a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/agenda/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_multi_agenda a where a.stts='".$where['stts']."' ".$query_add." order by a.id_multi_agenda DESC
		LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/agenda/detail/".$h->id_multi_agenda."/".$h->tipe_user."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_agenda_pengawas($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_unit_kerja']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_unit_kerja'] = $filter['id_unit_kerja']; 
				$where['tanggal'] = $filter['tanggal']; 
				$query_add = "and a.id_unit_kerja='".$where['id_unit_kerja']."' and 
				SUBSTRING(DATE_FORMAT(FROM_UNIXTIME(a.tanggal-3600*7), '%d/%m/%Y'),1,10)='".$where['tanggal']."'";
			}
		}

		$tot_hal = $this->db->query("select * from dlmbg_pengawas_agenda a where a.stts='".$where['stts']."' ".$query_add."");
		$config['base_url'] = base_url() . 'web/agenda_pengawas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select * from dlmbg_pengawas_agenda a where a.stts='".$where['stts']."' ".$query_add." order by a.id_pengawas_agenda DESC
		LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/agenda_pengawas/detail/".$h->id_pengawas_agenda."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_detail_agenda($id_param,$tipe)
	{
		$hasil="";
		$w="";
		if($tipe=="dinas")
		{
			$w = $this->db->query("select a.id_multi_agenda, a.judul, a.tipe_user, a.isi, a.tanggal, b.bidang, c.nama_admin_dinas as usr from dlmbg_multi_agenda a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_dinas c on a.id_user=c.id_admin_dinas where a.id_multi_agenda='".$id_param."' and a.stts='1'");
		}
		else
		{
			$w = $this->db->query("select a.id_multi_agenda, a.judul, a.tipe_user, a.isi, a.tanggal, b.bidang, c.nama_super_admin as usr from dlmbg_multi_agenda a left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_super c on a.id_user=c.id_admin_super where a.id_multi_agenda='".$id_param."' and a.stts='1'");
		}
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div><div style="float:none; width:380px; font-size:12px; font-weight:bold; padding-top:5px;">
			Ditulis oleh : '.$h->usr.' - Bidang : '.$h->bidang.'
			</div>
			<div id="news-list-detail">
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_detail_agenda_pengawas($id_param)
	{
		$hasil="";
		$w="";
		$w = $this->db->query("select a.id_pengawas_agenda, a.judul, a.isi, a.tanggal, b.unit_kerja, c.nama_user_pengawas as usr from dlmbg_pengawas_agenda a left join dlmbg_super_unit_kerja b on a.id_unit_kerja=b.id_super_unit_kerja left join dlmbg_user_pengawas c on a.id_user_pengawas=c.id_user_pengawas where a.id_pengawas_agenda='".$id_param."' and a.stts='1'");
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div><div style="float:none; width:380px; font-size:12px; font-weight:bold; padding-top:5px;">
			Ditulis oleh : '.$h->usr.' - Unit Kerja : '.$h->unit_kerja.'
			</div>
			<div id="news-list-detail">
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_index_download($limit,$offset,$filter=array())
	{
		$hasil="";
		$where['stts'] = 1;
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_bidang']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_bidang'] = $filter['id_bidang']; 
				$query_add = "and a.id_bidang='".$where['id_bidang']."'";
			}
		}

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_dinas_download",$where);
		$config['base_url'] = base_url() . 'web/download/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$w = $this->db->query("select a.judul_file, a.id_dinas_download, b.bidang, c.nama_admin_dinas from dlmbg_dinas_download a left join 
		dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_dinas c on a.id_admin_dinas=c.id_admin_dinas where a.stts='1' 
		".$query_add." order by a.id_dinas_download DESC limit ".$offset.",".$limit."");
		
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>Oleh : ".$h->nama_admin_dinas." - Dinas : ".$h->bidang."</h4>
			<a href='".base_url()."web/download/get/".$h->id_dinas_download."/".url_title(strtolower($h->judul_file))."'' title='".$h->judul_file."'>".$h->judul_file."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_get_download($id_param)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_dinas_download'] = $id_param;
		$acak = rand(1,9999999999);
		$w = $this->db->get_where("dlmbg_dinas_download",$where)->row();
		if (file_exists("./asset/file/".$w->nama_file."")) 
		{
			$data = file_get_contents("./asset/file/".$w->nama_file."");
			$name = url_title($acak.'_'.$w->judul_file.'_'.$w->nama_file);
			force_download($name, $data);
		}
		else
		{
			$hasil="File tidak ditemukan";
		}
	}
	 
	public function generate_index_buku_tamu($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_super_buku_tamu",$where);
		$config['base_url'] = base_url() . 'web/buku_tamu/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_super_buku_tamu','desc')->get_where("dlmbg_super_buku_tamu",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<div id='label-buku-tamu'>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='content-buku-tamu'><img src='".base_url()."asset/theme/".$_SESSION['site_theme']."/images/user-icon.png'>".$h->pesan."<div class='cleaner_h0'></div></div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='label-buku-tamu'>Oleh : ".$h->nama." | Kontak : ".$h->kontak."</div>";
			$hasil .= '<div class="cleaner_h10"></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_detail_artikel_sekolah($id_param)
	{
		$hasil="";
		$w="";
		
		$w = $this->db->query("select a.id_sekolah_artikel, a.judul, a.isi, a.gambar, a.tanggal, b.nama_sekolah, c.nama_operator as usr from dlmbg_sekolah_artikel a left join dlmbg_sekolah_profil b on a.id_sekolah_profil=b.id_sekolah_profil left join dlmbg_admin_sekolah c on a.id_admin_sekolah=c.id_admin_sekolah where a.id_sekolah_artikel='".$id_param."' and a.stts='1'");
		
		foreach($w->result() as $h)
		{
			$hasil .= '
			<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div>
			<div class="cleaner_h10"></div>
			<span style="float:none; width:380px; font-size:12px; font-weight:bold; text-align:right; padding-top:3px;">
			Ditulis oleh : '.$h->usr.' - Sekolah : '.$h->nama_sekolah.'
			</span>
			<div id="news-list-detail">
			<img src="'.base_url().'asset/images/artikel-sekolah/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_detail_artikel_uptd($id_param)
	{
		$hasil="";
		$w="";
		
		$w = $this->db->query("select a.id_uptd_artikel, a.judul, a.isi, a.gambar, a.tanggal, b.kecamatan, c.nama_operator as usr from dlmbg_uptd_artikel a left join dlmbg_super_kecamatan b on a.id_kecamatan=b.id_super_kecamatan left join dlmbg_admin_uptd c on a.id_admin_uptd=c.id_admin_uptd where a.id_uptd_artikel='".$id_param."' and a.stts='1'");
		
		foreach($w->result() as $h)
		{
			$hasil .= '
			<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div>
			<div class="cleaner_h10"></div>
			<span style="float:none; width:380px; font-size:12px; font-weight:bold; text-align:right; padding-top:3px;">
			Ditulis oleh : '.$h->usr.' - UPTD Pendidikan Kecamatan : '.$h->kecamatan.'
			</span>
			<div id="news-list-detail">
			<img src="'.base_url().'asset/images/artikel-uptd/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_detail_artikel_pengawas($id_param)
	{
		$hasil="";
		$w="";
		
		$w = $this->db->query("select a.id_pengawas_artikel, a.judul, a.isi, a.gambar, a.tanggal, b.unit_kerja, c.nama_user_pengawas as usr from dlmbg_pengawas_artikel a left join dlmbg_super_unit_kerja b on a.id_super_unit_kerja=b.id_super_unit_kerja left join dlmbg_user_pengawas c on a.id_user_pengawas=c.id_user_pengawas where a.id_pengawas_artikel='".$id_param."' and a.stts='1'");
		
		foreach($w->result() as $h)
		{
			$hasil .= '
			<div id="detail-title-news">'.$h->judul.'<div class="cleaner_h10"></div></div>
			<div class="cleaner_h10"></div>
			<span style="float:none; width:380px; font-size:12px; font-weight:bold; text-align:right; padding-top:3px;">
			Ditulis oleh : '.$h->usr.' - Unit Kerja : '.$h->unit_kerja.'
			</span>
			<div id="news-list-detail">
			<img src="'.base_url().'asset/images/artikel-pengawas/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4>
			'.$h->isi.'
			</div>';
		}
		return $hasil;
	}
	 
	public function generate_index_artikel_sekolah($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_sekolah_artikel",$where);
		$config['base_url'] = base_url() . 'web/artikel_sekolah/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_sekolah_artikel','desc')->get_where("dlmbg_sekolah_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/artikel-sekolah/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/artikel_sekolah/detail/'.$h->id_sekolah_artikel.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/artikel_sekolah/detail/'.$h->id_sekolah_artikel.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_artikel_uptd($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		if($this->session->userdata("by_id_kecamatan")!="")
		{
			$where['id_kecamatan'] = $this->session->userdata("by_id_kecamatan");
		}

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_uptd_artikel",$where);
		$config['base_url'] = base_url() . 'web/artikel_uptd/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_uptd_artikel','desc')->get_where("dlmbg_uptd_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/artikel-uptd/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/artikel_uptd/detail/'.$h->id_uptd_artikel.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/artikel_uptd/detail/'.$h->id_uptd_artikel.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_artikel_pengawas($limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		if($this->session->userdata("by_id_unit_kerja")!="semua")
		{
			$where['id_super_unit_kerja'] = $this->session->userdata("by_id_unit_kerja");
		}

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_pengawas_artikel",$where);
		$config['base_url'] = base_url() . 'web/artikel_pengawas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_pengawas_artikel','desc')->get_where("dlmbg_pengawas_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/artikel-pengawas/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/artikel_pengawas/detail/'.$h->id_pengawas_artikel.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/artikel_pengawas/detail/'.$h->id_pengawas_artikel.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_artikel_per_sekolah($id_param,$limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_sekolah_profil'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_sekolah_artikel",$where);
		$config['base_url'] = base_url() . 'web/artikel_sekolah/sekolah/'.$where['id_sekolah_profil'].'';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_sekolah_artikel','desc')->get_where("dlmbg_sekolah_artikel",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/artikel-sekolah/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/artikel_sekolah/detail/'.$h->id_sekolah_artikel.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/artikel_sekolah/detail/'.$h->id_sekolah_artikel.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_hasil_polling()
	{
		$hasil="";
		$where['aktif'] = 1;
		$w = $this->db->get_where("dlmbg_super_pertanyaan_poll",$where);
		foreach($w->result() as $h)
		{
			$hasil .= "<div class='cleaner_h20'></div><b>".$h->pertanyaan."</b><div class='cleaner_h10'></div>";
		}
		$hasil .= "<br>";
		$where_jawaban['id_pertanyaan'] = $h->id_super_pertanyaan_poll;
		$jawaban_polling = $this->db->get_where("dlmbg_super_jawaban_poll",$where_jawaban);
		
		$jum = $this->db->query("select SUM(jum) as jum from 
		dlmbg_super_jawaban_poll where id_pertanyaan='".$where_jawaban['id_pertanyaan']."'")->row();
		
		$hasil .= '<table style="border-collapse:collapse; width:100%;" cellpadding="5">';
		foreach($jawaban_polling->result() as $jawaban)
		{
			$pr = 0;
			if($jawaban->jum!=0)
			{
				$pr = sprintf("%2.1f",(($jawaban->jum/$jum->jum)*100));
			}
			$gbr = $pr * 1.5;
			$hasil .= "<tr><td width='100'><b>".$jawaban->jawaban."</b></td><td width='250'>
			<img src='".base_url()."asset/theme/".$_SESSION['site_theme']."/images/vote.jpg' width='".$gbr."' height='20'>
			</td><td width='70'>".$pr." %<br></td></tr>";
		}
		$hasil .= '</table>';
		$hasil .= "<div class='cleaner_h10'></div>Hasil berdasarkan dari ".$jum->jum." orang responden.";
		return $hasil;
	}
	 
	public function generate_index_data_kepegawaian($limit,$offset)
	{
		$hasil="";
		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->query("select a.nama, a.nip, a.jabatan, b.bidang, a.kontak from dlmbg_super_kepegawaian a 
		left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang order by a.id_bidang DESC");
		$config['base_url'] = base_url() . 'web/data_kepegawaian/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nip, a.jabatan, b.bidang, a.kontak from dlmbg_super_kepegawaian a 
		left join dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang order by a.id_bidang DESC LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<div id='label-buku-tamu'><b>NAMA : ".$h->nama."</b></div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='content-buku-tamu'><img src='".base_url()."asset/theme/".$_SESSION['site_theme']."/images/user-icon.png'>
			<table>
				<tr><td width='60'>NIP</td><td width='20'>:</td><td>".$h->nip."</td></tr>
				<tr><td>JABATAN</td><td>:</td><td>".$h->jabatan."</td></tr>
				<tr><td>BIDANG</td><td>:</td><td>".$h->bidang."</td></tr>
			</table>
			<div class='cleaner_h0'></div></div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='label-buku-tamu'>KONTAK : ".$h->kontak."</div>";
			$hasil .= '<div class="cleaner_h10"></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_data_pengawas_sekolah($limit,$offset)
	{
		$hasil="";
		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->query("select a.nama, a.nip, a.jabatan, b.unit_kerja, a.kontak from dlmbg_super_pengawas_sekolah a 
		left join dlmbg_super_unit_kerja b on a.id_unit_kerja=b.id_super_unit_kerja order by a.id_unit_kerja DESC");
		$config['base_url'] = base_url() . 'web/pengawas_sekolah/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nip, a.jabatan, b.unit_kerja, a.kontak from dlmbg_super_pengawas_sekolah a 
		left join dlmbg_super_unit_kerja b on a.id_unit_kerja=b.id_super_unit_kerja order by a.id_unit_kerja DESC LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<div id='label-buku-tamu'><b>NAMA : ".$h->nama."</b></div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='content-buku-tamu'><img src='".base_url()."asset/theme/".$_SESSION['site_theme']."/images/user-icon.png'>
			<table>
				<tr><td width='60'>NIP</td><td width='20'>:</td><td>".$h->nip."</td></tr>
				<tr><td>JABATAN</td><td>:</td><td>".$h->jabatan."</td></tr>
				<tr><td>UNIT KERJA</td><td>:</td><td>".$h->unit_kerja."</td></tr>
			</table>
			<div class='cleaner_h0'></div></div>";
			$hasil .= '<div class="cleaner_h0"></div>';
			$hasil .= "<div id='label-buku-tamu'>KONTAK : ".$h->kontak."</div>";
			$hasil .= '<div class="cleaner_h10"></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_berita_dinas($id_param,$limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_bidang'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_multi_berita",$where);
		$config['base_url'] = base_url() . 'web/berita_dinas/get/'.$id_param.'/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_multi_berita','desc')->get_where("dlmbg_multi_berita",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div id="news-list">
			<img src="'.base_url().'asset/images/berita/thumb/'.$h->gambar.'" />
			<h4>'.generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal)).' WIB</h4><h1><a href="'.base_url().'web/berita_dinas/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'">'.$h->judul.'</a></h1>
			'.substr($h->isi,0,200).'.... <a href="'.base_url().'web/berita_dinas/detail/'.$h->id_multi_berita.'/'.$h->tipe_user.'/'.url_title(strtolower($h->judul)).'"><b>(Baca Selengkapnya)</b></a>
			</div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_pengumuman_dinas($id_param,$limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_bidang'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_multi_pengumuman",$where);
		$config['base_url'] = base_url() . 'web/pengumuman_dinas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_multi_pengumuman','desc')->get_where("dlmbg_multi_pengumuman",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/pengumuman_dinas/detail/".$h->id_multi_pengumuman."/".$h->tipe_user."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_agenda_dinas($id_param,$limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_bidang'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_multi_agenda",$where);
		$config['base_url'] = base_url() . 'web/agenda_dinas/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->order_by('id_multi_agenda','desc')->get_where("dlmbg_multi_agenda",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>".generate_tanggal(gmdate('d/m/Y-H:i:s',$h->tanggal))." WIB</h4>
			<a href='".base_url()."web/agenda_dinas/detail/".$h->id_multi_agenda."/".$h->tipe_user."/".url_title(strtolower($h->judul))."'' title='".$h->judul."'>".$h->judul."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_download_dinas($id_param,$limit,$offset)
	{
		$hasil="";
		$where['stts'] = 1;
		$where['id_bidang'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_dinas_download",$where);
		$config['base_url'] = base_url() . 'web/download/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] =5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.judul_file, a.id_dinas_download, b.bidang, c.nama_admin_dinas from dlmbg_dinas_download a left join 
		dlmbg_super_bidang b on a.id_bidang=b.id_super_bidang left join dlmbg_admin_dinas c on a.id_admin_dinas=c.id_admin_dinas where a.stts='1' 
		and a.id_bidang = '".$where['id_bidang']."' limit ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<li><h4>Oleh : ".$h->nama_admin_dinas." - Dinas : ".$h->bidang."</h4>
			<a href='".base_url()."web/download/get/".$h->id_dinas_download."/".url_title(strtolower($h->judul_file))."'' title='".$h->judul_file."'>".$h->judul_file."</a></li>";
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_data_sekolah($limit,$offset,$filter=array())
	{
		$hasil="";
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$query_add = "";
			}
			else if($filter['id_jenjang_pendidikan']!="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."'";
			}
			else if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']!="semua")
			{
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_kecamatan='".$where['id_kecamatan']."'";
			}
			else
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."' and a.id_kecamatan='".$where['id_kecamatan']."'";
			}
		}

		$tot_hal = $this->db->query("select a.id_sekolah_profil, a.npsn, a.nama_sekolah, a.status_sekolah, b.pendidikan, c.kecamatan from 
		dlmbg_sekolah_profil a left join dlmbg_super_jenjang_pendidikan b on
		a.id_jenjang_pendidikan=b.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan c on a.id_kecamatan=c.id_super_kecamatan 
		".$query_add."");
		$config['base_url'] = base_url() . 'web/data_sekolah/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.id_sekolah_profil, a.npsn, a.nama_sekolah, a.status_sekolah, b.pendidikan, c.kecamatan 
		from dlmbg_sekolah_profil a left join dlmbg_super_jenjang_pendidikan b on
		a.id_jenjang_pendidikan=b.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan c on a.id_kecamatan=c.id_super_kecamatan 
		 ".$query_add." order by a.id_sekolah_profil DESC LIMIT ".$offset.",".$limit."");
		
		$hasil .= '<table style="border-collapse:collapse;" cellpadding="10" cellspacing="0" border="1" width="100%">';
		$hasil .= '<tr align="center" bgcolor="#F2F2F2">
					<td>No</td>
					<td>Nama Sekolah</td>
					<td>Jenjang Pendidikan</td>
					<td>Kecamatan</td></tr>';
		$i=1;
		foreach($w->result() as $h)
		{
			$hasil .= '<tr>
					<td>'.$i.'</td>
					<td><a href="'.base_url().'web/data_sekolah/profil/'.$h->id_sekolah_profil.'/'.strtolower(url_title($h->nama_sekolah)).'">
					'.$h->nama_sekolah.'</a></td>
					<td>'.$h->pendidikan.'</td>
					<td>'.$h->kecamatan.'</td></tr>';
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_detail_data_sekolah($id_param)
	{
		$hasil="";

		$w = $this->db->query("select a.id_sekolah_profil, a.npsn, a.nama_sekolah, a.status_sekolah, b.pendidikan, c.kecamatan, a.visi_misi, 
		a.alamat, a.email from dlmbg_sekolah_profil a left join dlmbg_super_jenjang_pendidikan b on
		a.id_jenjang_pendidikan=b.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan c on a.id_kecamatan=c.id_super_kecamatan 
		where a.id_sekolah_profil='".$id_param."'");
		
		if($w->num_rows==0)
		{
			return FALSE;
		}
		
		$hasil .= '<table style="border-collapse:collapse;" cellpadding="8" cellspacing="0" border="0" width="100%">';
		foreach($w->result() as $h)
		{
			$hasil .= '<tr valign="top"><td width="100">Nama Sekolah</td><td>:</td><td>'.$h->nama_sekolah.'</td>';
			$hasil .= '<tr valign="top"><td>NPSN</td><td>:</td><td>'.$h->npsn.'</td>';
			$hasil .= '<tr valign="top"><td>Status</td><td>:</td><td>'.$h->status_sekolah.'</td>';
			$hasil .= '<tr valign="top"><td>Jenjang</td><td>:</td><td>'.$h->pendidikan.'</td>';
			$hasil .= '<tr valign="top"><td>Visi & Misi</td><td>:</td><td>'.$h->visi_misi.'</td>';
			$hasil .= '<tr valign="top"><td>Alamat</td><td>:</td><td>'.$h->alamat.'</td>';
			$hasil .= '<tr valign="top"><td>Kecamatan</td><td>:</td><td>'.$h->kecamatan.'</td>';
			$hasil .= '<tr valign="top"><td>Email/HP</td><td>:</td><td>'.$h->email.'</td>';
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_galeri_sekolah($id_param,$limit,$offset)
	{
		$hasil="";
		$where['id_sekolah'] = $id_param;
		$where['stts'] = 1;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_sekolah_galeri_sekolah",$where);
		$config['base_url'] = base_url() . 'web/galeri_sekolah/sekolah/'.$id_param.'/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$w = $this->db->get_where("dlmbg_sekolah_galeri_sekolah",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div class="border-photo-gallery-index"><div class="hide-photo-gallery-index"><a href="'.base_url().'asset/images/galeri-sekolah/medium/'.$h->gambar.'" rel="galeri" title="'.$h->judul.'"><img src="'.base_url().'asset/images/galeri-sekolah/thumb/'.$h->gambar.'" title="'.$h->judul.'" /></a></div></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_galeri_uptd($id_param,$limit,$offset)
	{
		$hasil="";
		$where['id_kecamatan'] = $id_param;
		$where['stts'] = 1;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->get_where("dlmbg_uptd_galeri_uptd",$where);
		$config['base_url'] = base_url() . 'web/galeri_uptd/kecamatan/'.$id_param.'/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$w = $this->db->get_where("dlmbg_uptd_galeri_uptd",$where,$limit,$offset);
		foreach($w->result() as $h)
		{
			$hasil .= '<div class="border-photo-gallery-index"><div class="hide-photo-gallery-index"><a href="'.base_url().'asset/images/galeri-uptd/medium/'.$h->gambar.'" rel="galeri" title="'.$h->judul.'"><img src="'.base_url().'asset/images/galeri-uptd/thumb/'.$h->gambar.'" title="'.$h->judul.'" /></a></div></div>';
		}
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_kecamatan()
	{
		$hasil="";
		$w = $this->db->get("dlmbg_super_kecamatan");
		$hasil .= '<table style="border-collapse:collapse;" cellpadding="7" cellspacing="0" border="1" width="100%">';
		$hasil .= '<tr align="center" bgcolor="#F2F2F2">
					<td width="20">No</td>
					<td>Kecamatan</td></tr>';
		$i=1;
		foreach($w->result() as $h)
		{
			$hasil .= '<tr>
					<td>'.$i.'</td>
					<td><a href="'.base_url().'web/galeri_uptd/kecamatan/'.$h->id_super_kecamatan.'/'.strtolower(url_title($h->kecamatan)).'">
					'.$h->kecamatan.'</a></td>';
			$i++;
		}
		$hasil .= '</table>';
		return $hasil;
	}
	 
	public function generate_index_guru_sekolah($id_param,$limit,$offset)
	{
		$hasil="";
		$where['id_sekolah'] = $id_param;

		$page=$offset;
		if(!$page):
		$offset = 0;
		else:
		$offset = $page;
		endif;

		$tot_hal = $this->db->query("select a.nama, a.jk, a.status_pns, a.golongan, a.tugas, b.nama_sekolah, a.tempat_lahir, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_sekolah_guru a left join dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil
		left join dlmbg_super_jenjang_pendidikan c on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan 
		where a.id_sekolah='".$where['id_sekolah']."'");
		$config['base_url'] = base_url() . 'web/data_guru/sekolah/'.$id_param.'/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);
		
		$hasil .= "<table width='100%' style='border-collapse:collapse;' cellpadding='8' cellspacing='0' border='1' width='100%'>
					<tr bgcolor='#F2F2F2' align='center'>
					<td>No.</td>
					<td>Nama</td>
					<td>Jenis Kelamin</td>
					<td>Status PNS</td>
					<td>Golongan</td>
					<td>Tugas Sebagai</td>
					<td>Tempat Tugas</td>
					<td>Tempat Lahir</td>
					<td>Usia</td>
					<td>MK</td>
					</tr>";
		$i = $offset+1;
		$w = $this->db->query("select a.nama, a.jk, a.status_pns, a.golongan, a.tugas, b.nama_sekolah, a.tempat_lahir, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_sekolah_guru a left join dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil
		left join dlmbg_super_jenjang_pendidikan c on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan 
		where a.id_sekolah='".$where['id_sekolah']."' order by a.nama ASC LIMIT ".$offset.",".$limit."");
		foreach($w->result() as $h)
		{
			$hasil .= "<tr>
					<td>".$i."</td>
					<td>".$h->nama."</td>
					<td>".$h->jk."</td>
					<td>".$h->status_pns."</td>
					<td>".$h->golongan."</td>
					<td>".$h->tugas."</td>
					<td>".$h->nama_sekolah."</td>
					<td>".$h->tempat_lahir."</td>
					<td>".selisih_tanggah($h->tanggal_lahir,date("m/d/Y"))."</td>
					<td>".selisih_tanggah($h->tanggal_bertugas,date("m/d/Y"))."</td>
					</tr>";
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_guru($limit,$offset,$filter=array())
	{
		$hasil="";
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$query_add = "";
			}
			else if($filter['id_jenjang_pendidikan']!="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."'";
			}
			else if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']!="semua")
			{
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_kecamatan='".$where['id_kecamatan']."'";
			}
			else
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."' and a.id_kecamatan='".$where['id_kecamatan']."'";
			}
		}

		$tot_hal = $this->db->query("select a.nama, a.nip, a.status_pns, a.golongan, a.tugas, b.nama_sekolah, a.tempat_lahir, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_sekolah_guru a left join dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil
		left join dlmbg_super_jenjang_pendidikan c on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan 
		".$query_add."");
		$config['base_url'] = base_url() . 'web/data_guru/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nip, a.nuptk, a.status_pns, a.golongan, a.tugas, b.nama_sekolah, a.tempat_lahir, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_sekolah_guru a left join dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil
		left join dlmbg_super_jenjang_pendidikan c on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan  
		 ".$query_add." order by a.nama ASC LIMIT ".$offset.",".$limit."");
		
		$hasil .= "<table width='100%' style='border-collapse:collapse;' cellpadding='8' cellspacing='0' border='1' width='100%'>
					<tr bgcolor='#F2F2F2' align='center'>
					<td>No.</td>
					<td>Nama</td>
					<td>NIP</td>
					<td>Status Kepegawaian</td>
					<td>Golongan</td>
					<td>Tugas Sebagai</td>
					<td>Tempat Tugas</td>
					<td>NUPTK</td>
					<td>Usia</td>
					<td>MK</td>
					</tr>";
		$i = $offset+1;
		foreach($w->result() as $h)
		{
			$hasil .= "<tr>
					<td>".$i."</td>
					<td>".$h->nama."</td>
					<td>".$h->nip."</td>
					<td>".$h->status_pns."</td>
					<td>".$h->golongan."</td>
					<td>".$h->tugas."</td>
					<td>".$h->nama_sekolah."</td>
					<td>".$h->nuptk."</td>
					<td>".selisih_tanggah($h->tanggal_lahir,date("m/d/Y"))."</td>
					<td>".selisih_tanggah($h->tanggal_bertugas,date("m/d/Y"))."</td>
					</tr>";
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_pegawai($limit,$offset,$filter=array())
	{
		$hasil="";
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_kecamatan']=="semua")
			{
				$query_add = "";
			}
			else
			{
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_kecamatan='".$where['id_kecamatan']."'";
			}
		}

		$tot_hal = $this->db->query("select a.nama, a.nip, a.status_pns, a.golongan, a.tugas, b.kecamatan, a.kontak, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_uptd_pegawai a left join dlmbg_super_kecamatan b on a.id_kecamatan=b.id_super_kecamatan
		".$query_add."");
		$config['base_url'] = base_url() . 'web/data_pegawai/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nip, a.status_pns, a.golongan, a.tugas, b.kecamatan, a.kontak, 
		a.tanggal_lahir, a.tanggal_bertugas from dlmbg_uptd_pegawai a left join dlmbg_super_kecamatan b on a.id_kecamatan=b.id_super_kecamatan 
		 ".$query_add." order by a.nama ASC LIMIT ".$offset.",".$limit."");
		
		$hasil .= "<table width='100%' style='border-collapse:collapse;' cellpadding='8' cellspacing='0' border='1' width='100%'>
					<tr bgcolor='#F2F2F2' align='center'>
					<td>No.</td>
					<td>Nama</td>
					<td>NIP</td>
					<td>Status Kepegawaian</td>
					<td>Golongan</td>
					<td>Tugas Sebagai</td>
					<td>Kecamatan</td>
					<td>No Telp</td>
					<td>Usia</td>
					<td>MK</td>
					</tr>";
		$i = $offset+1;
		foreach($w->result() as $h)
		{
			$hasil .= "<tr>
					<td>".$i."</td>
					<td>".$h->nama."</td>
					<td>".$h->nip."</td>
					<td>".$h->status_pns."</td>
					<td>".$h->golongan."</td>
					<td>".$h->tugas."</td>
					<td>".$h->kecamatan."</td>
					<td>".$h->kontak."</td>
					<td>".selisih_tanggah($h->tanggal_lahir,date("m/d/Y"))."</td>
					<td>".selisih_tanggah($h->tanggal_bertugas,date("m/d/Y"))."</td>
					</tr>";
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_siswa($limit,$offset,$filter=array())
	{
		$hasil="";
		$query_add = "";
		if(!empty($filter))
		{
			if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$query_add = "";
			}
			else if($filter['id_jenjang_pendidikan']!="semua" &&  $filter['id_kecamatan']=="semua")
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."'";
			}
			else if($filter['id_jenjang_pendidikan']=="semua" &&  $filter['id_kecamatan']!="semua")
			{
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_kecamatan='".$where['id_kecamatan']."'";
			}
			else
			{
				$where['id_jenjang_pendidikan'] = $filter['id_jenjang_pendidikan']; 
				$where['id_kecamatan'] = $filter['id_kecamatan']; 
				$query_add = "where a.id_jenjang_pendidikan='".$where['id_jenjang_pendidikan']."' and a.id_kecamatan='".$where['id_kecamatan']."'";
			}
		}

		$tot_hal = $this->db->query("select a.nama, a.nisn, a.kelas, b.nama_sekolah, c.pendidikan, d.kecamatan from dlmbg_sekolah_siswa a left join 
		dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil left join dlmbg_super_jenjang_pendidikan c 
		on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan d on a.id_kecamatan=d.id_super_kecamatan
		".$query_add."");
		$config['base_url'] = base_url() . 'web/data_siswa/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 4;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nisn, a.kelas, b.nama_sekolah, c.pendidikan, d.kecamatan from dlmbg_sekolah_siswa a left join 
		dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil left join dlmbg_super_jenjang_pendidikan c 
		on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan d on a.id_kecamatan=d.id_super_kecamatan
		 ".$query_add." order by a.nama ASC LIMIT ".$offset.",".$limit."");
		
		$hasil .= "<table width='100%' style='border-collapse:collapse;' cellpadding='8' cellspacing='0' border='1' width='100%'>
					<tr bgcolor='#F2F2F2' align='center'>
					<td>No.</td>
					<td>NISN</td>
					<td>Nama Peserta Didik</td>
					<td>Kelas</td>
					<td>Nama Sekolah</td>
					<td>Kecamatan Sekolah</td>
					<td>Jenjang Pendidikan</td>
					</tr>";
		$i = $offset+1;
		foreach($w->result() as $h)
		{
			$hasil .= "<tr>
					<td>".$i."</td>
					<td>".$h->nisn."</td>
					<td>".$h->nama."</td>
					<td>".$h->kelas."</td>
					<td>".$h->nama_sekolah."</td>
					<td>".$h->kecamatan."</td>
					<td>".$h->pendidikan."</td>
					</tr>";
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	 
	public function generate_index_siswa_sekolah($id_param,$limit,$offset)
	{
		$hasil="";

		$tot_hal = $this->db->query("select a.nama, a.nisn, a.kelas, b.nama_sekolah, c.pendidikan, d.kecamatan from dlmbg_sekolah_siswa a left join 
		dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil left join dlmbg_super_jenjang_pendidikan c 
		on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan d on a.id_kecamatan=d.id_super_kecamatan
		where a.id_sekolah='".$id_param."'");
		$config['base_url'] = base_url() . 'web/data_siswa/index/';
		$config['total_rows'] = $tot_hal->num_rows();
		$config['per_page'] = $limit;
		$config['uri_segment'] = 5;
		$config['first_link'] = 'First';
		$config['last_link'] = 'Last';
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Prev';
		$this->pagination->initialize($config);

		$w = $this->db->query("select a.nama, a.nisn, a.kelas, b.nama_sekolah, c.pendidikan, d.kecamatan from dlmbg_sekolah_siswa a left join 
		dlmbg_sekolah_profil b on a.id_sekolah=b.id_sekolah_profil left join dlmbg_super_jenjang_pendidikan c 
		on a.id_jenjang_pendidikan=c.id_super_jenjang_pendidikan left join dlmbg_super_kecamatan d on a.id_kecamatan=d.id_super_kecamatan
		 where a.id_sekolah='".$id_param."' order by a.nama ASC LIMIT ".$offset.",".$limit."");
		
		$hasil .= "<table width='100%' style='border-collapse:collapse;' cellpadding='8' cellspacing='0' border='1' width='100%'>
					<tr bgcolor='#F2F2F2' align='center'>
					<td>No.</td>
					<td>NISN</td>
					<td>Nama Peserta Didik</td>
					<td>Kelas</td>
					<td>Nama Sekolah</td>
					<td>Kecamatan Sekolah</td>
					<td>Jenjang Pendidikan</td>
					</tr>";
		$i = $offset+1;
		foreach($w->result() as $h)
		{
			$hasil .= "<tr>
					<td>".$i."</td>
					<td>".$h->nisn."</td>
					<td>".$h->nama."</td>
					<td>".$h->kelas."</td>
					<td>".$h->nama_sekolah."</td>
					<td>".$h->kecamatan."</td>
					<td>".$h->pendidikan."</td>
					</tr>";
			$i++;
		}
		$hasil .= '</table>';
		$hasil .= '<div class="cleaner_h20"></div>';
		$hasil .= $this->pagination->create_links();
		return $hasil;
	}
	
	
}

/* End of file app_global_model.php */