import request from '@/utils/request'

export function getNotice() {
  return request({
    url: '/user/notice',
    method: 'get'
  })
}
