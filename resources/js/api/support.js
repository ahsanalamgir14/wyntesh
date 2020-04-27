import request from '@/utils/request'

export function fetchUserOpenedList(query) {
  return request({
    url: '/user/tickets/opened',
    method: 'get',
    params: query
  })
}

export function fetchUserClosedList(query) {
  return request({
    url: '/user/tickets/closed',
    method: 'get',
    params: query
  })
}

export function getConversations(id) {
  return request({
    url: '/user/ticket/'+id+'/conversations',
    method: 'get'
  })
}

export function openSupportTicket(data) {
  return request({
    url: '/user/tickets',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function addUserConversationMessage(data) {
  return request({
    url: '/user/tickets/add/message',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}


export function closeSupportTicket(data) {
  return request({
    url: '/user/tickets/close',
    method: 'post',
    data
  })
}
