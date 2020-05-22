import request from '@/utils/request'

export function getWelcomeLetter() {
  return request({
    url: '/user/welcome-letter',
    method: 'get'
  })
}
