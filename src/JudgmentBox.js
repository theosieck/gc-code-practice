const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentResp from './PresentResp';
import Codes from './Codes';
import Rows from './Rows';
import CommentBox from './CommentBox';

class JudgmentBox extends Component {
    state = {
        rows:[],
        activeSelect:'',
        codes:[0,0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','',''],
        doComment:false,
        comment:""
    }

    divStyle = {
        marginTop: '50px'
    };

    handleCodeButton = (code) => {
        if(code) {
            const codeKey = code[0]
            const selection = this.state.activeSelect ? this.state.activeSelect : 'No selection'
            this.setState((prevState) => ({
                rows: prevState.rows.filter((row) => row.code != code).concat({
                    'text':selection,
                    'code':code
                }),
                activeSelect:''
            }))
            this.state.codes[codeKey] = 1;
            this.state.excerpts[codeKey] = selection;
            if(document.getSelection) {
                if(document.getSelection().removeAllRanges) {
                    document.getSelection().removeAllRanges()
                }
            } else if(document.selection) {
                if(document.selection.empty) {
                    document.selection.empty()
                }
            } else {
                console.log("no empty function")
            }
        }
    }
    
    handleSelection = (selection) => {
        this.state.activeSelect = selection
    }

    handleDelete = (e) => {
        e.preventDefault()
        const code = e.target.id
        this.setState((prevState) => ({
            rows: prevState.rows.filter((row) => row.code[0] != code),
            excerpts: prevState.excerpts.map((excerpt,i) => i == code ? '' : excerpt),
            codes: prevState.codes.map((num,i) => i == code ? 0 : num)
        }))
    }

    handleCommentButton = (e) => {
        e.preventDefault()
        this.setState((prevState) => ({
            doComment:!prevState.doComment,
            comment:""
        }))
    }

    handleComment = (comment) => {
        this.setState(() => ({comment}))
    }

    handleNext = (e) => {
        e.preventDefault();
        this.setState(() => ({
            rows:[],
            activeSelect:'',
            codes:[0,0,0,0,0,0,0,0,0],
            excerpts:['','','','','','','','',''],
            doComment:false,
            comment:""
        }))
        this.props.handleNext(this.state.excerpts,this.state.codes,this.state.comment);
    }

    render() {
        return (
            <div>
                <div style={this.divStyle}>
                <Grid container direction="row" justify="space-between" alignItems="flex-start">
                    <Grid item xs={8} zeroMinWidth>
                    <PresentResp
                        respId={ this.props.respId }
                        response={ this.props.response }
                        handleSelection={this.handleSelection}
                    />
                    </Grid>
                    <Grid item xs={4}>
                    <Codes
                        codes={this.props.codes}
                        handleButton={this.handleCodeButton}
                        state={this.state}
                    />
                    </Grid>
                </Grid>
                </div>
                <div style={{marginTop:"25px"}}>
                {!this.state.doComment &&
                    <button onClick={this.handleCommentButton}>Add A Comment</button>
                }
                {this.state.doComment &&
                    <CommentBox
                        handleComment={this.handleComment}
                        handleCommentButton={this.handleCommentButton}
                    />
                }
                </div>
                <div style={{marginTop:"25px"}}>
                <Rows 
                    rows={this.state.rows}
                    handleDelete={this.handleDelete}
                    showDelete={true}
                />
                </div>
                <button style={{marginTop:"10px"}} onClick={this.handleNext}>Next</button>
            </div>
        );
    }
}

export default JudgmentBox