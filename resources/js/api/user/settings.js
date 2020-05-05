import request from '@/utils/request'

export function getSettings() {
  return request({
    url: '/user/settings',
    method: 'get'
  })
}
