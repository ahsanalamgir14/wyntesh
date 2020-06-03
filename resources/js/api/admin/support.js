
import request from '@/utils/request'

export function fetchOpenedList(query) {
  return request({
    url: '/admin/tickets/opened',
    method: 'get',
    params: query
  })
}

export function fetchClosedList(query) {
  return request({
    url: '/admin/tickets/closed',
    method: 'get',
    params: query
  })
}

export function getAdminConversations(id) {
  return request({
    url: '/admin/ticket/'+id+'/conversations',
    method: 'get'
  })
}

export function closeUserSupportTicket(data) {
  return request({
    url: '/admin/tickets/close',
    method: 'post',
    data
  })
}

export function addAdminConversationMessage(data) {
  return request({
    url: '/admin/tickets/add/message',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}
