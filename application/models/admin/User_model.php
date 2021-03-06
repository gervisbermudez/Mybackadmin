<?php 
class User_model extends MY_model {

    public $id = FALSE;
    public $username;
    public $password;
    public $lastseen;
    public $email;
    public $status;
    public $id_user_group;
    public $is_logged_in = false;
    public $user_group;
    public $level;
    public $date_created;
    public $is_map = false;
    private $field = ['id', 'username', 'password', 'email', 'lastseen', 'id_user_group', 'status'];
	private $table_name = 'user';

	public function __construct()
	{
		parent::__construct();
	}

	public function map($id)
	{
		$user = $this->get_data(array('id' => $id), $this->table_name);
        $user = $user[0];
        if (!$user) {
			return FALSE;
        }
        $this->id = $user['id'];

        // Set User data
        $query = $this->get_data(array('id_user' => $this->id), 'user_data');
        if ($query) {
           foreach ($query as $key => $value) {
                $propname = $value['_key'];
                $this->{$propname} = $value['_value'];
            }
        }
        // Set User permisions data
        $query = $this->get_data( array('id_user' => $this->id), 'user_permisions');
        if ($query) {
           foreach ($query as $key => $value) {
                $this->{$value['permision']} = $value['value'];
            }
        }

        $this->username         = $user['username'];
        $this->password         = $user['password'];
        $this->lastseen         = DateTime::createFromFormat('Y-m-d H:i:s',$user['lastseen']);
        $this->email            = $user['email'];
        $this->id_user_group    = $user['id_user_group'];
        $this->status           = $user['status'];
        $this->date_created     = DateTime::createFromFormat('Y-m-d H:i:s', $user['date_created']);
        $this->is_map           = true;

        $this->load->model('admin/User_Group_model');
        $user_group = new User_Group_model();
        $this->user_group = $user_group->map($this->id_user_group);
        $this->level = $this->user_group->level;

        return $this;
	}

	public function create($insert = false)
	{
        $date = new DateTime();
        if ($insert && is_array($insert)) {
		    return $this->set_data($insert, $this->table_name) ? $this : false;            
        }
        $insert = array(
            'username'              => $this->username,
            'password'              => $this->password,
            'email'                 => $this->email,
            'lastseen'              => $date->format('Y-m-d H:i:s'),
            'id_user_group'         => $this->id_user_group,
            'created_from_ip'       => $this->input->ip_address(),
            'updated_from_ip'       => $this->input->ip_address(),
            'date_created'          => $date->format('Y-m-d H:i:s'),
            'date_updated'          => $date->format('Y-m-d H:i:s')
        );
        if($this->set_data($insert, $this->table_name)){
          $this->id = $this->get_data($insert , $this->table_name)[0]['id'];
          return $this;  
        }
        return false;
	}

	public function update()
	{
        $update = array(
            'username'              => $this->username,
            'password'              => $this->password,
            'email'                 => $this->email,
            'id_user_group'         => $this->id_user_group
        );

        $where = array('id' => $this->id);

        if($this->update_data($where, $update, $this->table_name)){
            $user_data = array(
                'nombre'          => $this->nombre,
                'apellido'        => $this->apellido,
                'direccion'       => $this->direccion,
                'telefono'        => $this->telefono,
                'identificacion'  => $this->identificacion,
                'avatar'          => $this->avatar,
            );
             
            return $this->update_userdata($user_data) ? $this : false;
        }
        return false;
    }
    
    public function update_userdata($data = false, $table_name = 'user_data')
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $key => $value) {
            $where = array(
                '_key' => $key,
                'id_user' => $this->id 
            );
            $update = array(
                '_key' => $key,
                '_value' => $value,
                'id_user' => $this->id
            );
            if(!$this->update_data($where, $update, $table_name)){
                return false;
            }
        }
        return true;
    }
	public function set_status($status = FALSE)
	{
		if (!$this->id) {
			return FALSE;
		}
		$where = array('id' => $this->id);
		if ($status === '0' || $status === '1') {
			$update = array('status' => $status);
			$this->status = $status;
		}else{
			if ($this->status === '1') {
				$update = array('status' => '0');
				$this->status = '0';
			}else{
				$update = array('status' => '1');
				$this->status = '1';
			}
		}
		return $this->update_data($where, $update, $this->table_name);
	}

	public function delete()
	{
		if (!$this->id) {
			return FALSE;
		}
		$where = array('id' => $this->id);
		$this->delete_data($where, $this->table_name);
    }
    
    public function login($username, $password)
    {
		$query = $this->get_data(array('status' => 1, 'username' => $username), $this->table_name);
        if (!is_array($query)) {
           return false; 
        }
        $passwordhash = $query[0]['password'];
        if (!password_verify($password, $passwordhash)) {
            return false;
        }
        $this->is_logged_in = true;
        $this->map($query[0]['id']);
        $session = array('user' => (array) $this);
        $this->session->set_userdata($session);
        $date = new DateTime();
        $this->update_data(array('id'=>$this->id), array('lastseen' => $date->format('Y-m-d H:i:s')), $this->table_name);
        return $this->is_logged_in;
    }
    
    public function get_userdata($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    public function set_userdata($data = false, $table_name = 'user_data')
    {
        if (!is_array($data)) {
            return false;
        }
        
        $insert = array();

        foreach ($data as $key => $value) {
            $date = new DateTime();
            $temp = array(
                '_key' => $key,
                '_value' => $value,
                'id_user' => $this->id,
                'created_from_ip' => $this->input->ip_address(),
                'updated_from_ip' => $this->input->ip_address(),
                'date_created' => $date->format('Y-m-d H:i:s'),
                'date_updated' => $date->format('Y-m-d H:i:s')
            );
            array_push($insert, $temp);
        }
        return $this->db->insert_batch($table_name, $insert) ? $this : false;
    }

    public function get_user($data = 'all', $limit = '', $order = array('id', 'ASC'))
    {
        $this->db->select('user.*, user_group.name, user_group.level');
        $this->db->join('user_group', 'user.id_user_group = user_group.id');
        $limit ? $this->db->limit($limit) : null;
        if ($order!=='') {
            $this->db->order_by($order[0], $order[1]);
        }
        if ($data !== "all") {
            $query = $this->db->get_where('user', $data);
            if ($query->num_rows() > 0)
            {
                return $query->result_array(); 
            }
                return false; 
        }else{
            $query = $this->db->get('user');
            if ($query->num_rows() > 0)
            {
                return $query->result_array(); 
            }
                return false; 
        }
    }

    public function set_user($data)
    {
        return $this->db->insert('user', $data);
    }

    public function update_user($data, $id)
    {
        $this->db->where('id', $id);
        return $this->db->update('user', $data);
    }

    public function set_user_permisions($data)
    {
        if (!$data) {
            return false;
        }
        if (!$this->db->insert_batch('user_permisions', $data)) {
            return false;
        }       
        return true;
    }

    public function get_datauserstorage($data)
    {
        if ($data === 'all') {
            $query = $this->db->get('user_data'); 
            if ($query->num_rows() > 0)
            {
               return $query->result_array();
            }
        }else{
            $query = $this->db->get_where('user_data', $data);
            if ($query->num_rows() > 0){
                return $query->result_array();
            }
        }
        return false;
    }

    public function get_is_user_exist($id = false)
    {
        if ($id) {
            $query = $this->db->get_where('user', array('id' => $id));
            if ($query->num_rows() > 0){
                return true;
            }
        }
        return false;
    }
    
    public function delete_user($id_user = false)
    {
        if (!$id_user) {
            return false;
        }
        $this->db->where(array('id' => $id_user));
        if ($this->db->delete('user')) {
            //Borrar relaciones 
            $this->db->where(array('id_row' => $id_user, 'tablename' => 'user'));
            $this->db->delete('relations');
            //Borrar datauserstorage
            $this->db->where(array('id_user' => $id_user));
            $this->db->delete('user_data');
            //Borrar permisos
            $this->db->where(array('id_user' => $id_user));
            $this->db->delete('user_permisions');
            return true;
        }
        return false;
    }

    public function get_user_group($data = 'all', $limit = '', $order = array('id', 'ASC'))
    {
        $limit ? $this->db->limit($limit) : null;
        if ($order!=='') {
            $this->db->order_by($order[0], $order[1]);
        }
        if ($data === 'all') {
            $query = $this->db->get('user_group');   
            if ($query->num_rows() > 0)
            {
               return $query->result_array();
            }
        }else{
            $query = $this->db->get_where('user_group', $data);
            if ($query->num_rows() > 0){
                return $query->result_array();
            }
        }
        return false;
    }

    public function get_access($strPermisionName)
	{
		return $this->arrPermisions[$strPermisionName];
    }
    
    public function get_balance($date = 'all')
    {
        $data = array(
            'income' => 0,
            'expenses' => 0,
            'collections' => 0,
            'loans' => 0,
            'loans_sub' => 0,
            'loans_total' => 0
        );

        if(!$this->is_map){
            return $data;
        }

        switch ($date) {
            case 'today':

                $income = $this->get_query('SELECT SUM(`income`.`monto`) AS `income` FROM `income` WHERE DATE(fecha) = CURRENT_DATE AND `id_user`='.$this->id)[0];

                $expenses = $this->get_query('SELECT SUM(`expenses`.`monto`) AS `expenses` FROM `expenses` WHERE DATE(fecha) = CURRENT_DATE AND `id_user`='.$this->id)[0];

                $collections = $this->get_query('SELECT SUM(`loans_dues`.`monto_pagado`) AS `collections` FROM `loans_dues`, loans WHERE `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans_dues`.`fecha_pagado`=CURRENT_DATE AND `loans`.`id_prestamista`= '.$this->id)[0];

                $loans = $this->get_query('SELECT SUM(`loans`.`monto`) as `loans` FROM `loans` WHERE DATE(`registerdate`)= CURRENT_DATE AND `id_prestamista`='.$this->id)[0];

                $loans_sub = $this->get_query('SELECT SUM(`loans`.`subtotal`) as `loans_sub` FROM `loans` WHERE DATE(`registerdate`)= CURRENT_DATE AND `id_prestamista`='.$this->id)[0];

                $loans_total = $this->get_query('SELECT SUM(`loans`.`monto_total`) as `loans_total` FROM `loans` WHERE DATE(`registerdate`)= CURRENT_DATE AND `id_prestamista`='.$this->id)[0];

            break;

            case 'yesterday':
                $income = $this->get_query('SELECT SUM(`income`.`monto`) AS `income` FROM `income` WHERE DATE(fecha) = DATE(NOW() - INTERVAL 1 DAY) AND `id_user`='.$this->id)[0];

                $expenses = $this->get_query('SELECT SUM(`expenses`.`monto`) AS `expenses` FROM `expenses` WHERE DATE(fecha) = DATE(NOW() - INTERVAL 1 DAY) AND `id_user`='.$this->id)[0];

                $collections = $this->get_query('SELECT SUM(`loans_dues`.`monto_pagado`) AS `collections` FROM `loans_dues`, loans WHERE `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans_dues`.`fecha_pagado`=DATE(NOW() - INTERVAL 1 DAY) AND `loans`.`id_prestamista`= '.$this->id)[0];

                $loans = $this->get_query('SELECT SUM(`loans`.`monto`) as `loans` FROM `loans` WHERE DATE(`registerdate`)= DATE(NOW() - INTERVAL 1 DAY) AND `id_prestamista`='.$this->id)[0];

                $loans_sub = $this->get_query('SELECT SUM(`loans`.`subtotal`) as `loans_sub` FROM `loans` WHERE DATE(`registerdate`)= DATE(NOW() - INTERVAL 1 DAY) AND `id_prestamista`='.$this->id)[0];

                $loans_total = $this->get_query('SELECT SUM(`loans`.`monto_total`) as `loans_total` FROM `loans` WHERE DATE(`registerdate`)= DATE(NOW() - INTERVAL 1 DAY) AND `id_prestamista`='.$this->id)[0];

            break;
            
            default:
                $income = $this->get_query("SELECT SUM(`income`.`monto`) AS `income` FROM `income` WHERE  DATE(fecha) $date AND `id_user`=".$this->id)[0];

                $expenses = $this->get_query("SELECT SUM(`expenses`.`monto`) AS `expenses` FROM `expenses` WHERE DATE(fecha)  $date AND `id_user`=".$this->id)[0];

                $collections = $this->get_query("SELECT SUM(`loans_dues`.`monto_pagado`) AS `collections` FROM `loans_dues`, loans WHERE DATE(`loans_dues`.`fecha_pagado`) $date AND `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans`.`id_prestamista`= ".$this->id)[0];

                $loans = $this->get_query("SELECT SUM(`loans`.`monto`) as `loans` FROM `loans` WHERE DATE(`registerdate`) $date AND `id_prestamista`=".$this->id)[0];

                $loans_sub = $this->get_query("SELECT SUM(`loans`.`subtotal`) as `loans_sub` FROM `loans` WHERE DATE(`registerdate`) $date AND `id_prestamista`=".$this->id)[0];

                $loans_total = $this->get_query("SELECT SUM(`loans`.`monto_total`) as `loans_total` FROM `loans` WHERE DATE(`registerdate`) $date  AND `id_prestamista`=".$this->id)[0];
                
            break;
        }
        $data['income'] = $income['income'] ? $income['income'] :  0;
        $data['expenses'] = $expenses['expenses'] ? $expenses['expenses'] : 0;
        $data['collections'] = $collections['collections'] ? $collections['collections'] : 0;
        $data['loans'] = $loans['loans'] ? $loans['loans'] : 0;
        $data['loans_sub'] = $loans_sub['loans_sub'] ? $loans_sub['loans_sub'] : 0;
        $data['loans_total'] = $loans_total['loans_total'] ? $loans_total['loans_total'] : 0;
        

        $data['total'] = ($data['income'] + $data['collections']) - ($data['expenses'] + $data['loans']);

        return $data;
    }

    public function get_dues($date, $where = '')
    {
        
        switch ($date) {
            case 'today':
            $dues = $this->get_query("SELECT SUM(loans_dues.monto_pagado) AS 'recaudo', `clients`.`id` as 'id_cliente', `clients`.*, `loans_dues`.* FROM `loans_dues`, `loans`, `clients` WHERE $where `clients`.`id`=`loans`.`id_cliente` AND `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans_dues`.`fecha_pagado`=CURRENT_DATE AND `loans_dues`.`monto_pagado`>0 AND `loans`.`id_prestamista` = ".$this->id. ' GROUP BY `clients`.`id`');
                break;
            case 'yesterday':
                $dues = $this->get_query("SELECT SUM(loans_dues.monto_pagado) AS 'recaudo', `clients`.`id` as 'id_cliente', `clients`.*, `loans_dues`.* FROM `loans_dues`, `loans`, `clients` WHERE $where `clients`.`id`=`loans`.`id_cliente` AND `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans_dues`.`fecha_pagado`=DATE(NOW() - INTERVAL 1 DAY) AND `loans_dues`.`monto_pagado`>0 AND `loans`.`id_prestamista` = ".$this->id. ' GROUP BY `clients`.`id`');
            break;
            default:
                $dues = $this->get_query("SELECT SUM(loans_dues.monto_pagado) AS 'recaudo', `clients`.`id` as 'id_cliente', `clients`.*, `loans_dues`.* FROM `loans_dues`, `loans`, `clients` WHERE DATE(`loans_dues`.`fecha_pagado`) $date AND $where `clients`.`id`=`loans`.`id_cliente` AND `loans`.`id`=`loans_dues`.`id_prestamo` AND `loans_dues`.`monto_pagado`>0 AND `loans`.`id_prestamista` = ".$this->id. ' GROUP BY `clients`.`id`');
                break;
        }
        return $dues;
    }

    public function get_income_expenses($date = 'all')
    {
        $data = array(
            'income' => array(),
            'expenses' => array()
        );

        if(!$this->is_map){
            return $data;
        }

        switch ($date) {
            case 'today':
                $gastos = $this->get_query('SELECT * FROM `expenses` WHERE DATE(fecha) = CURRENT_DATE AND `id_user` ='.$this->id);
                $ingresos = $this->get_query('SELECT * FROM `income` WHERE DATE(fecha) = CURRENT_DATE AND `id_user` ='.$this->id);

            break;
            case 'yesterday':
                $gastos = $this->get_query('SELECT * FROM `expenses` WHERE DATE(fecha) = DATE(NOW() - INTERVAL 1 DAY) AND `id_user` ='.$this->id);
                $ingresos = $this->get_query('SELECT * FROM `income` WHERE DATE(fecha) = DATE(NOW() - INTERVAL 1 DAY) AND `id_user` ='.$this->id);

            break;
            default:
                $gastos = $this->get_query("SELECT * FROM `expenses` WHERE DATE(fecha) $date AND `id_user` =".$this->id);
                $ingresos = $this->get_query("SELECT * FROM `income` WHERE DATE(fecha) $date AND `id_user` =".$this->id);
                
            break;
        }
        $data['expenses'] = $gastos;
        $data['income'] = $ingresos;
        return $data;
    }
}
?>