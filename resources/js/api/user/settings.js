import request from '@/utils/request'

export function getSettings() {
  return request({
    url: '/user/settings',
    method: 'get'
  })
}


export function getPublicSettings() {
  return request({
    url: '/settings',
    method: 'get'
  })
}
