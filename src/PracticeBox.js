const { Component } = wp.element;

import Grid from '@material-ui/core/Grid';
import PresentExp from './PresentExp';
import Codes from './Codes';
import Rows from './Rows';
import ShowFeedback from './ShowFeedback';

class PracticeBox extends Component {
	genState = () => {
		const results = this.props.resultsObj
		let codes = [];
		let excerpts = [];
		let rows = [];
		for(let i=1;i<16;i++) {
			const codeNum = parseInt(results[`code${i}`])
			codes[i] = codeNum
			excerpts[i] = results[`excerpt${i}`]
			if(codeNum===1) {
				rows[i] = {
					text: excerpts[i],
					code: `${i}. ${this.props.codes[i]}`
				}
			}
		}
		return {
			rows,
			codes,
			excerpts,
			feedback: false,
			correctRows:[],
			missedRows:[],
			falsePositives:[]
		}
	}

    state = this.props.resultsObj ? this.genState() : {
        rows:[],
        activeSelect:'',
        codes:[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
        excerpts:['','','','','','','','','','','','','','',''],
				feedback: false,
				correctRows:[],
				missedRows:[],
				falsePositives:[]
    }

    divStyle = {
        marginTop: '50px'
    };

    handleCodeButton = (code) => {
        if(code) {
            const codeKey = isNaN(code[1]) ? code[0] : code[0]+code[1]
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
				console.log("deleting...")
        const code = e.target.id
				const codeKey = isNaN(code[1]) ? code[0] : code[0]+code[1]
				console.log(code)
        this.setState((prevState) => ({
            rows: prevState.rows.filter((row) => row.code != code),
            excerpts: prevState.excerpts.map((excerpt,i) => i == codeKey ? '' : excerpt),
            codes: prevState.codes.map((num,i) => i == codeKey ? 0 : num)
        }))
    }

		handleSubmit = (e) => {
			e.preventDefault();

			// sort excerpts into an array where each index is the code for the excerpt
			const allExcerpts = [];
			this.props.goldExcerpts.split("\r\n").forEach((excerpt) => {
				const excerptArray = excerpt.split("- ");
				allExcerpts[excerptArray[0]] = excerptArray[1];
			})
			// console.log(allExcerpts);

			// sort codes into correct, missed, and false positives
			const correctRows = [];
			const missedRows = [];
			const falsePositives = [];
			// console.log(this.props.codes)
			this.state.codes.forEach((code,index) => {
				if(index>0 && index<this.props.codes.length) {
					if(code) {
						// learner selected
						if(this.props.goldCodes.includes(index.toString())) {
							correctRows.push({
									'text':allExcerpts[index],
									'code':index + ". " + this.props.codes[index]
							})
							// correctRows.push(index);
						} else {
							falsePositives.push(index)
						}
					} else {
						// learner did not select
						if(this.props.goldCodes.includes(index.toString())) {
							missedRows.push({
									'text':allExcerpts[index],
									'code':index + ". " + this.props.codes[index]
							})
						}
					}
				}
			});

			// console.log(correctRows);

			this.setState(() => ({feedback:true,correctRows,missedRows,falsePositives}));
		}

    handleNext = (e) => {
        e.preventDefault();
        this.setState(() => ({
            rows:[],
            activeSelect:'',
            codes:[0,0,0,0,0,0,0,0,0,0,0,0,0,0],
            excerpts:['','','','','','','','','','','','','',''],
						feedback: false,
						correctRows:[],
						missedRows:[],
						falsePositives:[]
        }))

        this.props.handleNext(this.state.excerpts,this.state.codes,this.getCodeNums(this.state.correctRows),this.getCodeNums(this.state.missedRows),this.state.falsePositives);
    }

		getCodeNums = (rows) => {
			let codeNums = [];
			rows.forEach((row) => {
				const code = row.code
				codeNums.push(parseInt(isNaN(code[1]) ? code[0] : code[0]+code[1]))
			});
			return codeNums;
		}

    render() {
        return (
            <div>
                <div style={this.divStyle}>
                <Grid container direction="row" justify="space-between" alignItems="flex-start">
                    <Grid item xs={8} zeroMinWidth>
                    <PresentExp
                        expId={ this.props.expId }
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
                <Rows
                    rows={this.state.rows}
                    handleDelete={this.handleDelete}
                    showDelete={true}
                />
                </div>
                {!this.state.feedback && <button style={{marginTop:"10px"}} onClick={this.handleSubmit}>Submit</button>}
								{this.state.feedback &&
									<ShowFeedback
										correctRows={this.state.correctRows}
										missedRows={this.state.missedRows}
										falsePositives={this.state.falsePositives}
										codeLabels={this.props.codes}
										handleNext={this.handleNext}
									/>
								}
            </div>
        );
    }
}

export default PracticeBox
