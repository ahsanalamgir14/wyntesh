import { login, logout, getInfo ,admin_login} from '@/api/auth';
import { getSettings } from "@/api/user/settings";
import avatar from '@/assets/images/avatar.png'
import { getToken, setToken, removeToken } from '@/utils/auth';
import router, { resetRouter } from '@/router';
import store from '@/store';

const state = {
  id: null,
  token: getToken(),
  name: '',
  avatar: '',
  introduction: '',
  roles: [],
  permissions: [],
  settings: [],
  currency:{},
};

const mutations = {
  SET_ID: (state, id) => {
    state.id = id;
  },
  SET_TOKEN: (state, token) => {
    state.token = token;
  },
  SET_INTRODUCTION: (state, introduction) => {
    state.introduction = introduction;
  },
  SET_NAME: (state, name) => {
    state.name = name;
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar;
  },
  SET_ROLES: (state, roles) => {
    state.roles = roles;
  },
  SET_PERMISSIONS: (state, permissions) => {
    state.permissions = permissions;
  },
  SET_SETTINGS: (state, settings) => {
    state.settings = settings;
  },
  SET_CURRENCY: (state, currency) => {
    state.currency = currency;
  },
};

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo;
    return new Promise((resolve, reject) => {
      login({ username: username.trim(), password: password })
        .then(response => {
          let role=response.user.roles;
            commit('SET_TOKEN', response.access_token);
            commit('SET_PERMISSIONS', response.user.permissions);
            commit('SET_CURRENCY', response.user.currency);
            setToken(response.access_token);
            resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },

  admin_login({ commit }, userInfo) {
    const { username, token } = userInfo;
    console.log(userInfo);
    return new Promise((resolve, reject) => {
      admin_login({ username: username.trim(), token: token })
        .then(response => {
          let role=response.user.roles;
            commit('SET_TOKEN', response.access_token);
            commit('SET_PERMISSIONS', response.user.permissions);
            commit('SET_CURRENCY', response.user.currency);
            setToken(response.access_token);
            resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },


  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo(state.token)
        .then(response => {
          const  data = response;

          if (!data) {
            reject('Verification failed, please Login again.');
          }

          const { roles, name, introduction, permissions, id, profile_picture,currency } = data;
          // roles must be a non-empty array
          if (!roles || roles.length <= 0) {
            reject('getInfo: roles must be a non-null array!');
          }

          commit('SET_ROLES', roles);
          commit('SET_PERMISSIONS', permissions);
          commit('SET_NAME', name);
          
          if(profile_picture){
            commit('SET_AVATAR', profile_picture);
          }else{
            commit('SET_AVATAR', avatar);  
          }

          commit('SET_CURRENCY', currency);
          commit('SET_INTRODUCTION', introduction);
          commit('SET_ID', id);
          resolve(data);
        })
        .catch(error => {
          reject(error);
        });

        // getSettings().then(response => {
        //   commit('SET_SETTINGS', response.data);
        // });
    });
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      logout(state.token)
        .then(() => {
          commit('SET_TOKEN', '');
          commit('SET_ROLES', []);
          commit('SET_PERMISSIONS', []);
          removeToken();
          resetRouter();
          resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      commit('SET_TOKEN', '');
      commit('SET_ROLES', []);
      commit('SET_PERMISSIONS', []);
      removeToken();
      resolve();
    });
  },

  // Dynamically modify permissions
  changeRoles({ commit, dispatch }, role) {
    return new Promise(async resolve => {
      // const token = role + '-token';

      // commit('SET_TOKEN', token);
      // setToken(token);

      // const { roles } = await dispatch('getInfo');

      const roles = [role];
      const permissions = role.permissions.map(permission => permission);
      commit('SET_ROLES', roles);
      commit('SET_PERMISSIONS', permissions);
      resetRouter();

      // generate accessible routes map based on roles
      const accessRoutes = await store.dispatch('permission/generateRoutes', { roles, permissions });

      // dynamically add accessible routes
      router.addRoutes(accessRoutes);

      resolve();
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
