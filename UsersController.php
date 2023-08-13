<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Utility\Security;

class UsersController extends AppController
{
    public function novousuario(){

		$res = [];
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $requiredFields = ['nome', 'email', 'whatsapp', 'cpf', 'password'];
    
            // Verifica se todos os campos obrigatórios estão preenchidos
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $res = ['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos!'];
                    $this->set([
                        '_serialize' => ['res'],
                        'res' => $res
                    ]);
                    return;
                }
            }

            if (!filter_var($this->request->getData()['email'], FILTER_VALIDATE_EMAIL)) {
                $res = ['success' => false, 'message' => 'Informe um email válido'];
            }else{
                $verificaEmail   = $this->Users->find()->select(['email'])->where(['Users.email'        => $this->request->getData()['email']])->first();
                $verificaCelular = $this->Users->find()->select(['whatsapp'])->where(['Users.whatsapp'   => $this->request->getData()['whatsapp']])->first();
                $verificaCpf     = $this->Users->find()->select(['cpf'])->where(['Users.cpf'            => $this->request->getData()['cpf']])->first();
                //debug($verificaCpf);exit;
                if(!empty($verificaEmail)){
                    $res = ['success' => false, 'message' => 'Email já cadastrado. Informe outro email e tente novamente.'];
                }elseif($verificaCelular){
                    $res = ['success' => false, 'message' => 'Whatsapp já cadastrado. Informe outro número e tente novamente.'];
                }elseif($verificaCpf){
                    $res = ['success' => false, 'message' => 'CPF já cadastrado. Informe outro CPF e tente novamente.'];
                }else{
                    $user = $this->Users->newEntity($data);
                    $user->token = Security::hash($user->email.$user->id.date('d/m/y h:i:s'),'sha256', false);

                    if ($this->Users->save($user)) {
                        $res = ['success' => true, 'message' => 'Novo usuário cadastrado com sucesso'];
                    }elseif((isset($user->getErrors()['cpf']['custom'])) and ((!empty($user->getErrors()['cpf']['custom'])))){
                        $res = ['success' => false, 'message' => $user->getErrors()['cpf']['custom']];
                    }else {
                        //debug($user->getErrors()['cpf']['custom']);exit;
                        $res = ['success' => false, 'message' => 'Houve um erro ao salvar novo usuário'];
                    }
                }
            }
        }

        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }




    public function login(){
        $res = array();
        if($this->request->is('post')){
            $user = $this->Auth->Identify();
            if($user){
                $this->Auth->setUser($user);
                $res = ['success' => true, 'usuario' => $user];
                $this->set(compact('res'));
                $this->set('_serialize', ['res']);
            }else{
                $res['status'] = 1;
                $res['msg'] = 'Erro ao logar';
                $this->set(compact('res'));
                $this->set('_serialize', ['res']);
            }
        }
    }



    public function logout(){
        if($this->Auth->logout()){
            $res = ['success' => true, 'usuario' => 'Você foi desconectado'];
            $this->set(compact('res'));
            $this->set('_serialize', ['res']);
        }
    }

    public function boasvindas(){
        $res = ['success' => true, 'usuario' => 'Você está conectado como '.$this->Auth->user()['nome']];
        $this->set(compact('res'));
        $this->set('_serialize', ['res']);
    }



}
