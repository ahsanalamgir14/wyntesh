import request from '@/utils/request';

export function login(data) {
  return request({
    url: '/auth/login',
    method: 'post',
    data
  })
}

export function changePassword(data) {
  return request({
    url: '/auth/change-password',
    method: 'post',
    data
  })
}

export function resetPassword(data) {
  return request({
    url: '/auth/password/email',
    method: 'post',
    data
  })
}

export function getInfo(token) {
  return request({
    url: '/auth/me',
    method: 'get',
    params: { token }
  })
}

export function logout() {
  return request({
    url: '/auth/logout',
    method: 'post'
  })
}
