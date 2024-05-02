import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { usePage } from '@inertiajs/react'
import React,{ useEffect, useState } from 'react'
import { PencilSquareIcon} from '@heroicons/react/24/solid'
import TextInput from '@/Components/TextInput';
import ConversationItem from '@/Components/App/ConversationItem';
const ChatLayout = ({children}) => {
  const page = usePage()
  const conversations = page.props.conversations
  const selectedConversation = page.props.selectedConversation
  const [onlineUsers, setOnlineUsers] = useState([])
  const [localConversations, setLocalConversation] = useState([])
  const [sortedConversations, setSortedConversation] = useState([])

  const isUserOnline = (user_id) => onlineUsers[user_id]

  const onSearch = (_event) => {
    const search = _event.target.value.toLowercase()
    setLocalConversation(
      localConversations.filter(_conversation => {
        return _conversation.name.toLowercase().includes(search) 
      })
    )
  }

  useEffect(()=>{
    setLocalConversation(conversations)
  },[conversations])

  useEffect(()=>{
    setSortedConversation(
      localConversations.sort((a, b)=>{
        if(a.blocked_at && b.blocked_at){
          return a.blocked_at > b.blocked_at ? 1 : -1
        }else if(a.blocked_at){
          return 1
        }else if(b.blocked_at){
          return -1
        }

        if(a.last_message_date && b.last_message_date){
          return b.last_message_date.localeCompare(a.last_message_date)
        }else if(a.last_message_date){
          return -1
        }else if(b.last_message_date){
          return 1
        }else{
          return 0
        }

      })
    )
  },[localConversations])

  useEffect(()=>{
    Echo.join('online')
        .here((users)=>{
            console.log('here', users)
            //setOnlineUsers(users)
            const onlineUserObj = Object.fromEntries(
              users.map((user)=>[user.id,user])
            )
            setOnlineUsers(prevUsers => {
                return {
                  ...prevUsers,
                  ...onlineUserObj
                }
              })
        })
        .joining((user)=>{
            console.log('joining', user)
            //setOnlineUsers((prevUsers => [...prevUsers, user]))
            setOnlineUsers((prevUsers) => {
              const updatedUser = {...prevUsers}
              updatedUser[user.id] = user
              return updatedUser
            })
        })
        .leaving((user)=>{
            console.log('leaving', user)
            //setOnlineUsers((prevUsers)=>prevUsers.filter((_user) =>_user.id != user.id))
            setOnlineUsers((prevUsers) => {
              const updatedUser = {...prevUsers}
              delete updatedUser[user.id]
              return updatedUser
            })
        })
        .error((error)=>{
            console.error('error', error)
        });
    
    return () => Echo.leave('online')    
    
  },[])
  

  return (
    <div className='flex flex-1 w-full overflow-hidden'>
      <div
        className={` transition-all w-full sm:w-[200px] md:w-[300px] dark:bg-slate-800 flex flex-col overflow-hidden ${selectedConversation ? '-ml-[100%] sm:ml-0' : ''}`}
      >      
        <div className='flex items-center justify-between py-2 px-3 text-xl font-medium'>
          My conversation
          <div
            className='tooltip tooltip-left'
            data-tip="Create new group"
          >
            <button
              className='text-gray-400 hover:text-gray-200'
            >
    
              <PencilSquareIcon className="w-4 h-4 inline-block ml-2" />
            </button>
          </div>
        </div>
        <div className='p-3'>
          <TextInput
            onKeyUp={onSearch}
            placeholder="Filter users and groups"
            className="w-full"
          />
        </div>
        <div className='flex-1 overflow-auto dark:text-white'>
          {sortedConversations && 
            sortedConversations.map((conversation)=>{
              return (
                <ConversationItem 
                  key={`${conversation.is_group ? 'group_' : 'user_'}${conversation.id}`}
                  conversation={conversation}
                  online={!!isUserOnline(conversation.id)}
                  selectedConversation={selectedConversation}
                >
                  {conversation.last_message}
                </ConversationItem>
              )
            })
          }
        </div>
      </div>  
      <div className='flex flex-1 flex-col overflow-hidden'>
        {children}
      </div>
    </div>
  )
}

export default ChatLayout