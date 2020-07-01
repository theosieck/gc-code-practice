const { Component } = wp.element;

class CommentBox extends Component {
    state = {
        error:undefined,
        commentMessage:"",
        comments:""
    }

    handleRationaleObj = (e) => {
        e.preventDefault()
        const comment = e.target.elements.rationale.value.trim().replace(/\n\n/g, "<br> ").replace(/\n/g, "<br> ")
        // const oldComments = this.state.comments
        // const numWords = comment.split(" ").length + (!!oldComments ? oldComments.split(" ").length : 0)
        const numWords = comment.split(" ").length
        if (numWords > 125) {
            this.setState(() => ({error:"Please trim your comment down to 125 words"}))
        } else {
            this.setState(() => ({commentMessage:"Saving comment..."}))
            this.props.handleComment(comment)
            setTimeout(() => {
                this.setState(() => ({
                    error:undefined,
                    commentMessage:"Comment saved",
                    // comments: prevState.comments ? prevState.comments.concat(`<br>${comment} `) : `${comment} `
                }))
            }, 300)
        }
    }

    // handleTextChange = (e) => {
    //     e.preventDefault()
    //     console.log(e.target.rationale)
    //     // this.setState((prevState) => ({comments:prevState.comments.concat(e.target.elements.rationale.value)}))
    // }

    render() {
        return (
            <div id="rationale">
                
                {/*this.state.comments && 
                    <div>
                    <h2>Comments:</h2>
                    {ReactHtmlParser(this.state.comments)}
                    </div>*/
                }
                <h2>Comment:</h2>
                {this.state.error && <span style={{color:"red"}}>{this.state.error}</span>}
                <form onSubmit={this.handleRationaleObj}>
                    <textarea name="rationale" cols={40} rows={5} maxLength={1000} required placeholder={"125 words or less"} />
                    <input type="submit" value="Save Comment" />
                    <button style={{marginLeft:"20px"}} onClick={this.props.handleCommentButton}>Nevermind, I don't have a comment</button>
                </form>
                {this.state.commentMessage && <p>{this.state.commentMessage}</p>}
            </div>
        )
    }
}

export default CommentBox