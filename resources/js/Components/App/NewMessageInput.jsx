import React,{useEffect,useRef} from 'react'

const NewMessageInput = ({value,onChange,onSend}) => {
  const inputRef = useRef()
  const onInputKeyDown = (_event) => {
    if(_event.key === "Enter" && !_event.shiftKey){
      _event.preventDefault()
      onSend()
    }
  }

  const onChangeEvent = (_event) => {
    setTimeout(()=>{
      adjustHeight()
    },10)
    onChange(_event)
  }

  const adjustHeight = () => {
    setTimeout(()=>{
      inputRef.current.style.height = "auto"
      inputRef.current.style.height = `${inputRef.current.scrollHeight + 1}px`;
    },100)
  }

  useEffect(()=>{
    adjustHeight()
  },[value])

  return (
    <textarea
      ref={inputRef}
      value={value}
      rows="1"
      placeholder='Type a message'
      onKeyDown={onInputKeyDown}
      onChange={(_event)=>onChangeEvent(_event)}
      className='input input-bordered w-full rounded-r-none resize-none overflow-y-auto max-h-40'
    ></textarea>
  )
}

export default NewMessageInput