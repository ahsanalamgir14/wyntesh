import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/emails',
    method: 'get',
    params: query
  })
}

export function getAllEmail() {
  return request({
    url: '/admin/emails/all/',
    method: 'get'
  })
}

export function getEmail(id) {
  return request({
    url: '/admin/email/'+id,
    method: 'get'
  })
}

export function deleteEmail(id) {
  return request({
    url: '/admin/email/'+id+'/delete',
    method: 'delete'
  })
}

export function createEmail(data) {
  return request({
    url: '/admin/email',
    method: 'post',
    data
  })
}

export function updateEmail(data) {
  return request({
    url: '/admin/email/update',
    method: 'post',
    data
  })
}

