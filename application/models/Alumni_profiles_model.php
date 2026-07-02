<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alumni Profiles Model
 *
 * Mengelola data profil detail alumni termasuk:
 * - Data pribadi tambahan
 * - Kontak darurat
 * - Pendidikan lanjutan
 * - Skill & kompetensi
 * - Pengalaman organisasi
 * - Prestasi
 * - Preferensi karir
 *
 * @package Tracer Study
 * @subpackage Models
 */
class Alumni_profiles_model extends MY_Model {

    protected $table_name = 'alumni_profiles';
    protected $primary_key = 'id';
    protected $soft_delete = TRUE;
    protected $deleted_field = 'deleted_at';

    // Fillable fields
    protected $fillable = [
        'alumni_id',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'kewarganegaraan',
        'nama_kontak_darurat',
        'kontak_darurat_hubungan',
        'nomor_kontak_darurat',
        'pendidikan_tertinggi',
        'nama_institusi_lanjut',
        'jurusan_lanjut',
        'tahun_mulai_lanjut',
        'tahun_selesai_lanjut',
        'negara_institusi_lanjut',
        'skill_teknis',
        'skill_non_teknis',
        'sertifikasi',
        'bahasa_asing',
        'pengalaman_organisasi',
        'prestasi',
        'portofolio_url',
        'github_url',
        'website_pribadi',
        'posisi_diinginkan',
        'lokasi_kerja_diinginkan',
        'ekspektasi_gaji',
        'kesediaan_relokasi',
        'jenis_pekerjaan_diinginkan',
        'bio_singkat',
        'foto_profil_url',
        'cv_url'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get profile by alumni ID
     *
     * @param int $alumni_id Alumni ID
     * @return object|NULL Profile data or NULL if not found
     */
    public function getByAlumniId($alumni_id)
    {
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }

        $query = $this->db->get_where('alumni_profiles', ['alumni_id' => (int)$alumni_id]);
        return $query->row();
    }

    /**
     * Get or create profile for alumni
     * Jika profil belum ada, buat profil kosong baru
     *
     * @param int $alumni_id Alumni ID
     * @return object Profile data
     */
    public function getOrCreate($alumni_id)
    {
        $profile = $this->getByAlumniId($alumni_id);

        if (!$profile) {
            // Create empty profile
            $data = [
                'alumni_id' => (int)$alumni_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('alumni_profiles', $data);
            $profile_id = $this->db->insert_id();

            $profile = $this->find($profile_id);
        }

        return $profile;
    }

    /**
     * Update profile by alumni ID
     *
     * @param int $alumni_id Alumni ID
     * @param array $data Profile data to update
     * @return bool TRUE if successful
     */
    public function updateByAlumniId($alumni_id, $data)
    {
        $profile = $this->getOrCreate($alumni_id);

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('alumni_id', (int)$alumni_id);
        return $this->db->update('alumni_profiles', $data);
    }

    /**
     * Get profiles with filter
     *
     * @param array $filters Filter parameters
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array Paginated results
     */
    public function getProfilesWithFilter($filters = [], $page = 1, $per_page = 25)
    {
        $offset = ($page - 1) * $per_page;

        $this->db->select('ap.*, a.nim, a.nama, a.email, p.nama as prodi_nama');
        $this->db->from('alumni_profiles ap');
        $this->db->join('alumni a', 'ap.alumni_id = a.id', 'inner');
        $this->db->join('prodi p', 'a.prodi_id = p.id', 'left');

        if ($this->soft_delete) {
            $this->db->where('ap.deleted_at', NULL);
            $this->db->where('a.deleted_at', NULL);
        }

        // Filter by jenis kelamin
        if (!empty($filters['jenis_kelamin'])) {
            $this->db->where('ap.jenis_kelamin', $filters['jenis_kelamin']);
        }

        // Filter by status perkawinan
        if (!empty($filters['status_perkawinan'])) {
            $this->db->where('ap.status_perkawinan', $filters['status_perkawinan']);
        }

        // Filter by pendidikan tertinggi
        if (!empty($filters['pendidikan_tertinggi'])) {
            $this->db->where('ap.pendidikan_tertinggi', $filters['pendidikan_tertinggi']);
        }

        // Search by nama atau nim
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('a.nama', $filters['search']);
            $this->db->or_like('a.nim', $filters['search']);
            $this->db->or_like('a.email', $filters['search']);
            $this->db->group_end();
        }

        $total = $this->db->count_all_results('', FALSE);

        // Reset and rebuild for data
        $this->db->reset_query();
        $this->db->select('ap.*, a.nim, a.nama, a.email, p.nama as prodi_nama');
        $this->db->from('alumni_profiles ap');
        $this->db->join('alumni a', 'ap.alumni_id = a.id', 'inner');
        $this->db->join('prodi p', 'a.prodi_id = p.id', 'left');

        if ($this->soft_delete) {
            $this->db->where('ap.deleted_at', NULL);
            $this->db->where('a.deleted_at', NULL);
        }

        // Re-apply filters
        if (!empty($filters['jenis_kelamin'])) {
            $this->db->where('ap.jenis_kelamin', $filters['jenis_kelamin']);
        }
        if (!empty($filters['status_perkawinan'])) {
            $this->db->where('ap.status_perkawinan', $filters['status_perkawinan']);
        }
        if (!empty($filters['pendidikan_tertinggi'])) {
            $this->db->where('ap.pendidikan_tertinggi', $filters['pendidikan_tertinggi']);
        }
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('a.nama', $filters['search']);
            $this->db->or_like('a.nim', $filters['search']);
            $this->db->or_like('a.email', $filters['search']);
            $this->db->group_end();
        }

        $this->db->order_by('a.nama', 'ASC');
        $this->db->limit($per_page, $offset);

        $query = $this->db->get();
        $data = $query->result_array();

        // Decode JSON fields
        foreach ($data as &$row) {
            if (!empty($row['skill_teknis'])) {
                $row['skill_teknis_decoded'] = json_decode($row['skill_teknis'], TRUE) ?? explode(',', $row['skill_teknis']);
            }
            if (!empty($row['sertifikasi'])) {
                $row['sertifikasi_decoded'] = json_decode($row['sertifikasi'], TRUE) ?? [];
            }
            if (!empty($row['bahasa_asing'])) {
                $row['bahasa_asing_decoded'] = json_decode($row['bahasa_asing'], TRUE) ?? [];
            }
        }

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];
    }

    /**
     * Get statistics from alumni profiles
     *
     * @return array Statistics data
     */
    public function getStatistics()
    {
        $stats = [];

        // Total profiles
        $this->db->select('COUNT(*) as total');
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $stats['total_profiles'] = $this->db->get('alumni_profiles')->row()->total;

        // Gender distribution
        $this->db->select('jenis_kelamin, COUNT(*) as count');
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->group_by('jenis_kelamin');
        $gender_result = $this->db->get('alumni_profiles')->result_array();

        $stats['gender'] = ['L' => 0, 'P' => 0];
        foreach ($gender_result as $row) {
            if (!empty($row['jenis_kelamin'])) {
                $stats['gender'][$row['jenis_kelamin']] = $row['count'];
            }
        }

        // Marital status distribution
        $this->db->select('status_perkawinan, COUNT(*) as count');
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->group_by('status_perkawinan');
        $marital_result = $this->db->get('alumni_profiles')->result_array();

        $stats['marital_status'] = [];
        foreach ($marital_result as $row) {
            $stats['marital_status'][$row['status_perkawinan']] = $row['count'];
        }

        // Education level distribution
        $this->db->select('pendidikan_tertinggi, COUNT(*) as count');
        if ($this->soft_delete) {
            $this->db->where('deleted_at', NULL);
        }
        $this->db->group_by('pendidikan_tertinggi');
        $edu_result = $this->db->get('alumni_profiles')->result_array();

        $stats['education'] = [];
        foreach ($edu_result as $row) {
            $stats['education'][$row['pendidikan_tertinggi'] ?? 'unknown'] = $row['count'];
        }

        return $stats;
    }

    /**
     * Save skill data as JSON
     *
     * @param int $alumni_id Alumni ID
     * @param array $skills Array of skills
     * @return bool TRUE if successful
     */
    public function saveSkills($alumni_id, $skills, $type = 'teknis')
    {
        $field = ($type === 'non_teknis') ? 'skill_non_teknis' : 'skill_teknis';
        $data = [
            $field => is_array($skills) ? json_encode($skills) : $skills,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->updateByAlumniId($alumni_id, $data);
    }

    /**
     * Save certification data as JSON
     *
     * @param int $alumni_id Alumni ID
     * @param array $certifications Array of certifications
     * @return bool TRUE if successful
     */
    public function saveCertifications($alumni_id, $certifications)
    {
        $data = [
            'sertifikasi' => json_encode($certifications),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->updateByAlumniId($alumni_id, $data);
    }

    /**
     * Save organization experience as JSON
     *
     * @param int $alumni_id Alumni ID
     * @param array $organizations Array of organizations
     * @return bool TRUE if successful
     */
    public function saveOrganizations($alumni_id, $organizations)
    {
        $data = [
            'pengalaman_organisasi' => json_encode($organizations),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->updateByAlumniId($alumni_id, $data);
    }

    /**
     * Save achievements as JSON
     *
     * @param int $alumni_id Alumni ID
     * @param array $achievements Array of achievements
     * @return bool TRUE if successful
     */
    public function saveAchievements($alumni_id, $achievements)
    {
        $data = [
            'prestasi' => json_encode($achievements),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->updateByAlumniId($alumni_id, $data);
    }
}