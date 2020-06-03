import request from '@/utils/request'

export function getNotice() {
  return request({
    url: '/admin/notice',
    method: 'get'
  })
}

export function saveNotice(data) {
  return request({
    url: '/admin/notice',
    method: 'post',
    data
  })
}
