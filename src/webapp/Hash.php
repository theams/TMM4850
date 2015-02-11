<?ph
// this comment is made by the One - Bjorkto.
namespace tdt4237\webapp;

class Hash
{
    function __construct()
    {
    }

    static function make($plaintext)
    {
        //return hash('sha512', $plaintext);
        
        return password_hash($plaintext, PASSWORD_BCRYPT);
    }

    static function check($plaintext, $hash)
    {
        //return self::make($plaintext) === $hash;
        
        return password_verify($plaintext, $hash);
    }
}
