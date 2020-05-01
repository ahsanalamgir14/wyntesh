import request from '@/utils/request'

export function fetchList(query) {
  return request({
    url: '/admin/inquiries',
    method: 'get',
    params: query
  })
}

export function updateTag(data,id) {
  return request({
    url: '/admin/inquiry/'+id+'/update',
    method: 'post',
    data
  })
}

export function changeInquiryStatus(data) {
  return request({
    url: '/admin/inquiry/change-status',
    method: 'post',
    data
  })
}


export function deleteInquiry(id) {
  return request({
    url: '/admin/inquiry/'+id+'/delete',
    method: 'delete'
  })
}