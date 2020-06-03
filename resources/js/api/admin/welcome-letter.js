import request from '@/utils/request'

export function getWelcomeLetter() {
  return request({
    url: '/admin/welcome-letter',
    method: 'get'
  })
}

export function saveWelcomeLetter(data) {
  return request({
    url: '/admin/welcome-letter',
    method: 'post',
    data
  })
}
