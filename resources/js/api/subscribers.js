import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/subscribers',
    method: 'get',
    params: query
  })
}


export function deleteSubscriber(id) {
  return request({
    url: '/admin/subscriber/'+id+'/delete',
    method: 'delete'
  })
}