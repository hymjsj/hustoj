<?php defined('SYSPATH') or die('No direct script access.');
/**
 * user model for hust online judge
 *
 * @author freefcw
 */
class Model_User extends Model_Database {
    
    public function __construct()
    {
        parent::__construct();
        //will load database library into $this->db, you can leave it out if you don't need it
    }
    
    public function auth($username, $password)
    {
        $sql = 'SELECT * FROM users WHERE (user_id = :username) AND (password = :password)';
        $query = DB::query(Database::SELECT, $sql);
        $query->parameters(array(
            ':username' => $username,
            ':password' => $password
        ));

        $result = $query->execute();

        if ($result->count() == 0 ) return false;
        return true;
    }

    public function get_password($username)
    {
        $sql = 'SELECT password FROM users WHERE user_id = :username';
        $query = DB::query(Database::SELECT, $sql);
        $query->parameters(array(
            ':username' => $username
        ));

        $result = $query->execute(null, TRUE);
        if ($result->count() == 0) return null;
        return $result->current()->password;
    }
    /**
         *
         * @param <int> $user_id
         * @param <string> $new_password
         */
    public function changepassword($user_id, $new_password)
    {
        $this->_db->where('user_id', $user_id);
        $this->db->update('users', array('password' => $new_password));
    }
    /**
         * 通过用户名返回用户信息
         *
         * @param <type> $user_id
         * @return <mixed> user imformation
         */
    public function get_info_by_name($user_id)
    {
        $key = 'user-' . $user_id;
        $cache = Cache::instance();
        $data = $cache->get($key);
        if ($data != null) return $data;

        $query = DB::select()->where('user_id', '=', $user_id)->from('users');
        //$result = $query->as_object()->execute();
        $result = $query->execute();

        $ret = $result->current();

        $cache->set($key, $ret, 60);
        return $ret;
    }


    public function get_list($page_id, $per_page)
    {
        $key = 'user-rank' . $page_id;
        $cache = Cache::instance();
        $data = $cache->get($key);
        if ($data != null) return $data;
        
        $query = DB::select('user_id', 'nick', 'solved', 'submit')
                ->from('users')
                ->offset(($page_id-1) * $per_page)
                ->limit($per_page)
                ->order_by('solved', 'DESC');
               
        $result = $query->as_object()->execute();
        
        $ret = array();
        foreach($result as $r){
            $ret[] = $r;
        }

        $cache->set($key, $ret, array('user', 'rank'));
        return $ret;
    }

    /**
        *  所有的用户数
        *
        * @return <array> user
        */
    public function get_total()
    {
        // TODO: more params
        $key = 'user-total';
        $cache = Cache::instance();
        $data = $cache->get($key);
        
        if ($data != null) return $data;
        
        
        $sql = 'SELECT count(*) AS total FROM users';
        $result = $this->_db->query(Database::SELECT, $sql, TRUE);

        $ret = $result->current()->total;

        $cache->set($key, $ret, array('user','total'));
        return $ret;
    }

    public function update_information($user)
    {
        $result = DB::update('users')
            ->set(array(
            'password'=> Auth::instance()->hash($user['password']),
            'school'=> $user['school'],
            'email'=> $user['email'],
            'nick'=> $user['nick']))
            ->where('user_id', '=', $user['user_id'])
            ->execute(null, true);

        return $result;
    }

    public function add_user($user)
    {}
}